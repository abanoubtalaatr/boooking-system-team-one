<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function createDoctorDashboardPayment(
    User $doctor,
    Patient $patient,
    PaymentMethod $method,
    PaymentStatus $status,
    int $amountCents,
    int $commissionCents,
): Payment {
    $slot = AvailabilitySlot::query()
        ->where('doctor_id', $doctor->id)
        ->firstOrFail();
    $booking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'booking_date' => now()->addDay()->toDateString(),
        'status' => BookingStatus::Confirmed,
        'payment_status' => $status,
        'price' => number_format($amountCents / 100, 2, '.', ''),
    ]);

    return Payment::factory()->create([
        'booking_id' => $booking->id,
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'method' => $method,
        'status' => $status,
        'amount_cents' => $amountCents,
        'commission_bps' => 1000,
        'commission_amount_cents' => $commissionCents,
        'doctor_amount_cents' => $amountCents - $commissionCents,
        'paid_at' => in_array($status, [PaymentStatus::Succeeded, PaymentStatus::CashCollected], true) ? now() : null,
    ]);
}

test('doctor dashboard endpoints require authentication and doctor role', function () {
    $this->getJson('/api/doctor/dashboard')->assertUnauthorized();
    $this->getJson('/api/doctor/dashboard/payments')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/doctor/dashboard')->assertForbidden();
    $this->getJson('/api/doctor/dashboard/payments')->assertForbidden();
});

test('doctor dashboard contains only the authenticated doctor financial data', function () {
    [$doctor] = createBookableSlot();
    [$otherDoctor] = createBookableSlot();
    $patient = Patient::factory()->create();
    Setting::factory()->platformBookingCommission('10.00')->create();

    $cardPayment = createDoctorDashboardPayment(
        $doctor,
        $patient,
        PaymentMethod::Card,
        PaymentStatus::Succeeded,
        50000,
        5000,
    );
    createDoctorDashboardPayment(
        $doctor,
        $patient,
        PaymentMethod::Cash,
        PaymentStatus::CashCollected,
        30000,
        3000,
    );
    createDoctorDashboardPayment(
        $doctor,
        $patient,
        PaymentMethod::Card,
        PaymentStatus::Pending,
        20000,
        2000,
    );
    createDoctorDashboardPayment(
        $otherDoctor,
        $patient,
        PaymentMethod::Card,
        PaymentStatus::Succeeded,
        900000,
        90000,
    );

    $wallet = Wallet::factory()->create([
        'doctor_id' => $doctor->id,
        'balance_cents' => 42000,
    ]);
    WalletTransaction::factory()->create([
        'wallet_id' => $wallet->id,
        'payment_id' => $cardPayment->id,
        'booking_id' => $cardPayment->booking_id,
        'amount_cents' => 45000,
        'balance_after_cents' => 45000,
    ]);

    Sanctum::actingAs($doctor);

    $this->getJson('/api/doctor/dashboard')
        ->assertOk()
        ->assertJsonPath('data.doctor.id', $doctor->id)
        ->assertJsonPath('data.current_commission.card.basis_points', 1000)
        ->assertJsonPath('data.current_commission.cash.basis_points', 1000)
        ->assertJsonPath('data.wallet.balance_cents', 42000)
        ->assertJsonPath('data.bookings.total', 3)
        ->assertJsonPath('data.payments.total_transactions', 3)
        ->assertJsonPath('data.payments.completed_transactions', 2)
        ->assertJsonPath('data.payments.gross_revenue_cents', 80000)
        ->assertJsonPath('data.payments.platform_fees_cents', 8000)
        ->assertJsonPath('data.payments.doctor_net_revenue_cents', 72000)
        ->assertJsonPath('data.payments.card_net_revenue_cents', 45000)
        ->assertJsonPath('data.payments.cash_gross_collected_cents', 30000)
        ->assertJsonPath('data.payments.cash_commission_cents', 3000)
        ->assertJsonPath('data.payments.pending_card_cents', 20000)
        ->assertJsonCount(3, 'data.recent_bookings')
        ->assertJsonMissing(['gross_amount_cents' => 900000]);
});

test('doctor payment list cannot be expanded to another doctor using filters', function () {
    [$doctor] = createBookableSlot();
    [$otherDoctor] = createBookableSlot();
    $patient = Patient::factory()->create();
    $ownPayment = createDoctorDashboardPayment($doctor, $patient, PaymentMethod::Card, PaymentStatus::Succeeded, 50000, 5000);
    $otherPayment = createDoctorDashboardPayment($otherDoctor, $patient, PaymentMethod::Card, PaymentStatus::Succeeded, 90000, 9000);

    Sanctum::actingAs($doctor);

    $this->getJson("/api/doctor/dashboard/payments?doctor_id={$otherDoctor->id}")
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.uuid', $ownPayment->uuid)
        ->assertJsonPath('data.0.doctor.id', $doctor->id)
        ->assertJsonPath('data.0.platform_fee_cents', 5000)
        ->assertJsonMissing(['uuid' => $otherPayment->uuid])
        ->assertJsonMissingPath('data.0.provider.client_secret');
});
