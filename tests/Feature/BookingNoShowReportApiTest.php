<?php

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\RefundResultData;
use App\Enums\BookingStatus;
use App\Enums\NoShowReportStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\WalletTransactionType;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\BookingNoShowReportReviewedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows the booking doctor to submit one no-show report after the grace period', function (): void {
    $this->travelTo('2026-07-15 12:00:00');
    [$doctor, $booking] = noShowEligibleBooking();
    Sanctum::actingAs($doctor);

    $this->postJson("/api/doctor/bookings/{$booking->id}/no-show-reports", [
        'reason' => 'المريض لم يحضر الموعد ولم يرد على الاتصال.',
    ])->assertCreated()
        ->assertJsonPath('data.status', NoShowReportStatus::PendingReview->value)
        ->assertJsonPath('data.booking_id', $booking->id);

    $this->postJson("/api/doctor/bookings/{$booking->id}/no-show-reports", [
        'reason' => 'المريض لم يحضر الموعد للمرة الثانية.',
    ])->assertConflict()
        ->assertJsonPath('error.code', 'report_already_exists');
});

it('prevents early reports and reports for another doctor booking', function (): void {
    $this->travelTo('2026-07-15 11:30:00');
    [$doctor, $booking] = noShowEligibleBooking();
    Sanctum::actingAs($doctor);

    $this->postJson("/api/doctor/bookings/{$booking->id}/no-show-reports", [
        'reason' => 'المريض لم يحضر الموعد ولم يرد على الاتصال.',
    ])->assertConflict()->assertJsonPath('error.code', 'report_too_early');

    Sanctum::actingAs(User::factory()->create(['role' => 'doctor']));
    $this->postJson("/api/doctor/bookings/{$booking->id}/no-show-reports", [
        'reason' => 'محاولة تقديم بلاغ على حجز طبيب آخر.',
    ])->assertForbidden();
});

it('restores a collected cash commission exactly once when admin approves', function (): void {
    Notification::fake();
    $this->travelTo('2026-07-15 12:00:00');
    [$doctor, $booking, $slot] = noShowEligibleBooking();
    $admin = User::factory()->create(['role' => 'admin']);
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'patient_id' => $booking->patient_id,
        'doctor_id' => $doctor->id,
        'method' => PaymentMethod::Cash,
        'status' => PaymentStatus::CashCollected,
        'commission_amount_cents' => 1000,
        'doctor_amount_cents' => 9000,
        'amount_cents' => 10000,
    ]);
    $wallet = Wallet::factory()->create([
        'doctor_id' => $doctor->id,
        'balance_cents' => -1000,
        'payout_blocked' => true,
    ]);
    WalletTransaction::factory()->create([
        'wallet_id' => $wallet->id,
        'payment_id' => $payment->id,
        'booking_id' => $booking->id,
        'type' => WalletTransactionType::CashCommissionDebit,
        'amount_cents' => -1000,
        'balance_after_cents' => -1000,
        'idempotency_key' => "cash-commission:{$payment->uuid}",
    ]);
    $report = BookingNoShowReport::factory()->create([
        'booking_id' => $booking->id,
        'doctor_id' => $doctor->id,
    ]);
    Sanctum::actingAs($admin);

    $this->patchJson("/api/admin/no-show-reports/{$report->id}/approve", [
        'review_note' => 'تم التحقق من سجل التواصل.',
    ])->assertOk()->assertJsonPath('data.status', NoShowReportStatus::Approved->value);

    expect($booking->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Voided)
        ->and($wallet->fresh()->balance_cents)->toBe(0)
        ->and($slot->fresh()->is_booked)->toBeFalse()
        ->and(WalletTransaction::query()->where('idempotency_key', "no-show-commission-reversal:{$payment->uuid}")->count())->toBe(1);

    $this->patchJson("/api/admin/no-show-reports/{$report->id}/approve")
        ->assertConflict()->assertJsonPath('error.code', 'report_already_reviewed');
    expect($wallet->fresh()->balance_cents)->toBe(0);
    Notification::assertSentTo($doctor, BookingNoShowReportReviewedNotification::class);
});

it('refunds a successful card payment when admin approves', function (): void {
    Notification::fake();
    $this->travelTo('2026-07-15 12:00:00');
    [$doctor, $booking] = noShowEligibleBooking();
    $admin = User::factory()->create(['role' => 'admin']);
    $payment = Payment::factory()->create([
        'booking_id' => $booking->id,
        'patient_id' => $booking->patient_id,
        'doctor_id' => $doctor->id,
        'method' => PaymentMethod::Card,
        'status' => PaymentStatus::Succeeded,
        'amount_cents' => 10000,
        'commission_amount_cents' => 1000,
        'doctor_amount_cents' => 9000,
    ]);
    $wallet = Wallet::factory()->create(['doctor_id' => $doctor->id, 'balance_cents' => 9000]);
    WalletTransaction::factory()->create([
        'wallet_id' => $wallet->id,
        'payment_id' => $payment->id,
        'booking_id' => $booking->id,
        'type' => WalletTransactionType::CardCredit,
        'amount_cents' => 9000,
        'balance_after_cents' => 9000,
        'idempotency_key' => "card-credit:{$payment->uuid}",
    ]);
    $report = BookingNoShowReport::factory()->create([
        'booking_id' => $booking->id,
        'doctor_id' => $doctor->id,
    ]);
    $gateway = Mockery::mock(PaymentGatewayInterface::class);
    $gateway->shouldReceive('refund')->once()->andReturn(new RefundResultData(true, 'refund-no-show'));
    $this->app->instance(PaymentGatewayInterface::class, $gateway);
    Sanctum::actingAs($admin);

    $this->patchJson("/api/admin/no-show-reports/{$report->id}/approve")
        ->assertOk()->assertJsonPath('data.status', NoShowReportStatus::Approved->value);

    expect($booking->fresh()->status)->toBe(BookingStatus::Refunded)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($wallet->fresh()->balance_cents)->toBe(0);
});

it('keeps the booking unchanged when admin rejects the report', function (): void {
    Notification::fake();
    $this->travelTo('2026-07-15 12:00:00');
    [$doctor, $booking] = noShowEligibleBooking();
    $admin = User::factory()->create(['role' => 'admin']);
    $report = BookingNoShowReport::factory()->create([
        'booking_id' => $booking->id,
        'doctor_id' => $doctor->id,
    ]);
    Sanctum::actingAs($admin);

    $this->patchJson("/api/admin/no-show-reports/{$report->id}/reject", [
        'review_note' => 'لم يتم تقديم دليل كافٍ.',
    ])->assertOk()->assertJsonPath('data.status', NoShowReportStatus::Rejected->value);

    expect($booking->fresh()->status)->toBe(BookingStatus::Completed);
    Notification::assertSentTo($doctor, BookingNoShowReportReviewedNotification::class);
});

/**
 * @return array{User, Booking, AvailabilitySlot}
 */
function noShowEligibleBooking(): array
{
    $doctor = User::factory()->create(['role' => 'doctor']);
    $patient = Patient::factory()->create();
    $slot = AvailabilitySlot::factory()->create([
        'doctor_id' => $doctor->id,
        'day' => '2026-07-15',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'is_booked' => true,
        'reservation_status' => SlotReservationStatus::Booked,
    ]);
    $booking = Booking::factory()->create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'availability_slot_id' => $slot->id,
        'booking_date' => '2026-07-15',
        'booking_time' => '10:00:00',
        'status' => BookingStatus::Completed,
    ]);
    $slot->update(['reserved_booking_id' => $booking->id]);

    return [$doctor, $booking, $slot];
}
