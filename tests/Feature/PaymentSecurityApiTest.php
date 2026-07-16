<?php

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Jobs\ExpireBookingHolds;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->app->instance(PaymentGatewayInterface::class, mock(PaymentGatewayInterface::class));
});

it('requires patient authentication and idempotency headers', function (): void {
    [$doctor, $slot] = createBookableSlot();

    $this->postJson('/api/bookings', [
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'consultation_type' => 'clinic',
    ])->assertUnauthorized();

    Sanctum::actingAs(Patient::factory()->create(), ['*'], 'patient');
    $this->postJson('/api/bookings', [
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'consultation_type' => 'clinic',
    ])->assertUnprocessable()->assertJsonValidationErrors('idempotency_key');
});

it('rejects past slots inactive doctors and doctors without a positive price', function (string $invalidState): void {
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();

    if ($invalidState === 'past_slot') {
        $slot->update(['day' => now()->subDay()->toDateString()]);
    } elseif ($invalidState === 'inactive_doctor') {
        $doctor->doctorProfile()->update(['is_active' => false]);
    } else {
        $doctor->doctorProfile()->update(['price' => 0]);
    }

    Sanctum::actingAs($patient, ['*'], 'patient');
    $this->withHeader('Idempotency-Key', "invalid-{$invalidState}")
        ->postJson('/api/bookings', [
            'doctor_id' => $doctor->id,
            'availability_slot_id' => $slot->id,
            'consultation_type' => 'clinic',
        ])
        ->assertUnprocessable();
})->with(['past_slot', 'inactive_doctor', 'missing_price']);

it('protects checkout and payment status by patient ownership', function (): void {
    [$doctor, $slot] = createBookableSlot();
    $owner = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $owner, $doctor, $slot, 'owned-booking');
    $otherPatient = Patient::factory()->create();

    Sanctum::actingAs($otherPatient, ['*'], 'patient');
    $this->withHeader('Idempotency-Key', 'foreign-checkout')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'cash'])
        ->assertNotFound();

    Sanctum::actingAs($owner, ['*'], 'patient');
    $paymentId = $this->withHeader('Idempotency-Key', 'owner-checkout')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'cash'])
        ->assertSuccessful()
        ->json('data.id');
    Payment::query()->where('uuid', $paymentId)->firstOrFail()->update(['provider_client_secret' => 'must-not-leak']);
    $this->getJson("/api/payments/{$paymentId}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $paymentId)
        ->assertJsonMissingPath('data.client_secret')
        ->assertDontSee('must-not-leak');

    Sanctum::actingAs($otherPatient, ['*'], 'patient');
    $this->getJson("/api/payments/{$paymentId}")->assertNotFound();
});

it('validates checkout method and its independent idempotency key', function (): void {
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot, 'checkout-validation-booking');

    $this->withHeader('Idempotency-Key', '')
        ->postJson("/api/bookings/{$bookingId}/checkout", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['method', 'idempotency_key']);

    $this->withHeader('Idempotency-Key', 'invalid-method-checkout')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'crypto'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('method');
});

it('allows only the booking doctor to mark cash as collected', function (): void {
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot, 'doctor-owned-booking');
    $this->withHeader('Idempotency-Key', 'doctor-owned-cash')
        ->postJson("/api/bookings/{$bookingId}/checkout", ['method' => 'cash'])
        ->assertSuccessful();

    Sanctum::actingAs(User::factory()->doctor()->create());
    $this->postJson("/api/doctor/bookings/{$bookingId}/cash-collected")->assertForbidden();

    Sanctum::actingAs($doctor);
    $this->postJson("/api/doctor/bookings/{$bookingId}/cash-collected")
        ->assertSuccessful()
        ->assertJsonPath('data.status', PaymentStatus::CashCollected->value);
});

it('expires a held booking and keeps the compatibility booking flag synchronized', function (): void {
    [$doctor, $slot] = createBookableSlot();
    $patient = Patient::factory()->create();
    $bookingId = createBookingThroughApi($this, $patient, $doctor, $slot, 'expiring-booking');
    $booking = $patient->patientBookings()->findOrFail($bookingId);
    $booking->update(['hold_expires_at' => now()->subSecond()]);

    (new ExpireBookingHolds)->handle();

    expect($booking->fresh()->status)->toBe(BookingStatus::Expired)
        ->and($slot->fresh()->reservation_status)->toBe(SlotReservationStatus::Available)
        ->and($slot->fresh()->is_booked)->toBeFalse();
});

it('does not treat the public return URL as payment confirmation', function (): void {
    $this->getJson('/api/payments/paymob/return?success=true')
        ->assertStatus(202);

    expect(Payment::query()->count())->toBe(0);
});
