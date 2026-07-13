<?php

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\PaymentIntentData;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Wallet;
use App\Notifications\PaymentSucceededNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('holds a slot idempotently and prevents a second patient from booking it', function (): void {
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot);

    $duplicateId = createBookingThroughApi($this, $patient, $doctor, $slot);

    expect($duplicateId)->toBe($bookingId);
    expect($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Held)
        ->and($slot->fresh()->is_booked)->toBeTrue();

    $otherPatient = Patient::factory()->create();
    Sanctum::actingAs($otherPatient, ['*'], 'patient');
    $this->withHeader('Idempotency-Key', 'other-booking-key')
        ->postJson('/api/bookings', [
            'doctor_id' => $doctor->id,
            'availability_slot_id' => $slot->id,
            'consultation_type' => 'clinic',
        ])
        ->assertUnprocessable();
});

it('confirms cash checkout without crediting the doctor wallet', function (): void {
    $this->app->instance(PaymentGatewayInterface::class, Mockery::mock(PaymentGatewayInterface::class));
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot);

    $this->withHeader('Idempotency-Key', 'cash-checkout-key')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'cash'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', PaymentStatus::CashDue->value)
        ->assertJsonPath('data.method', 'cash');

    $this->assertDatabaseHas('bookings', [
        'id' => $bookingId,
        'status' => BookingStatus::Confirmed->value,
        'payment_status' => PaymentStatus::CashDue->value,
    ]);
    expect(Wallet::query()->where('doctor_id', $doctor->id)->exists())->toBeFalse();
    $payment = Payment::query()->where('booking_id', $bookingId)->firstOrFail();
    expect($payment->amount_cents)->toBe(50000)
        ->and($payment->commission_bps)->toBe(0)
        ->and($payment->commission_amount_cents)->toBe(0)
        ->and($payment->doctor_amount_cents)->toBe(50000);
});

it('creates a Paymob card intention and keeps confirmation for the webhook', function (): void {
    Setting::factory()->platformCardCommission('7.50')->create();
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('createIntent')->once()->andReturn(new PaymentIntentData(
        'intent-1', 'order-1', 'client-secret', 'https://checkout.test/pay',
    ));
    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot);

    $response = $this->withHeader('Idempotency-Key', 'card-checkout-key')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'card'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', PaymentStatus::Pending->value)
        ->assertJsonPath('data.client_secret', 'client-secret')
        ->assertJsonPath('data.checkout_url', 'https://checkout.test/pay');

    $payment = Payment::query()->where('uuid', $response->json('data.id'))->firstOrFail();
    expect($payment->booking->status)->toBe(BookingStatus::PendingPayment)
        ->and($payment->provider_intention_id)->toBe('intent-1')
        ->and($payment->commission_bps)->toBe(750)
        ->and($payment->commission_amount_cents)->toBe(3750)
        ->and($payment->doctor_amount_cents)->toBe(46250);
});

it('marks cash as collected and deducts the configured commission from the doctor wallet', function (): void {
    Notification::fake();
    Setting::factory()->platformCashCommission('12.00')->create();
    $this->app->instance(PaymentGatewayInterface::class, Mockery::mock(PaymentGatewayInterface::class));
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot, 'cash-commission-booking');
    $this->withHeader('Idempotency-Key', 'cash-commission-checkout')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'cash'])
        ->assertSuccessful();

    Sanctum::actingAs($doctor);
    $this->postJson("/api/doctor/bookings/{$bookingId}/cash-collected")
        ->assertSuccessful()
        ->assertJsonPath('data.status', PaymentStatus::CashCollected->value);

    expect(Wallet::query()->where('doctor_id', $doctor->id)->value('balance_cents'))->toBe(-6000);
    $payment = Payment::query()->where('booking_id', $bookingId)->firstOrFail();
    expect($payment->commission_bps)->toBe(1200)
        ->and($payment->commission_amount_cents)->toBe(6000)
        ->and($payment->doctor_amount_cents)->toBe(44000)
        ->and($doctor->wallet()->firstOrFail()->currency)->toBe('EGP')
        ->and($doctor->wallet()->firstOrFail()->payout_blocked)->toBeTrue()
        ->and($doctor->wallet()->firstOrFail()->canWithdraw())->toBeFalse();
    Notification::assertSentTo($patient, PaymentSucceededNotification::class);
    Notification::assertSentTo($doctor, PaymentSucceededNotification::class);
});
