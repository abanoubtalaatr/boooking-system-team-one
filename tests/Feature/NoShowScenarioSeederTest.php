<?php

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\NoShowScenarioSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(LazilyRefreshDatabase::class);

it('seeds and completes the cash no-show cancellation scenario', function (): void {
    Notification::fake();
    $this->seed(NoShowScenarioSeeder::class);

    $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
    $admin = User::query()->where('email', 'demo.admin@cure.test')->firstOrFail();
    $booking = Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->firstOrFail();
    $wallet = Wallet::query()->where('doctor_id', $doctor->id)->firstOrFail();

    expect($booking->status)->toBe(BookingStatus::Completed)
        ->and($booking->payment_status)->toBe(PaymentStatus::CashCollected)
        ->and($wallet->balance_cents)->toBe(-4500);

    Sanctum::actingAs($doctor);
    $reportId = $this->postJson("/api/doctor/bookings/{$booking->id}/no-show-reports", [
        'reason' => 'المريض لم يحضر الموعد ولم يرد على محاولات التواصل.',
    ])->assertCreated()->json('data.id');

    Sanctum::actingAs($admin);
    $this->patchJson("/api/admin/no-show-reports/{$reportId}/approve", [
        'review_note' => 'تم التحقق من البلاغ والموافقة على رد العمولة.',
    ])->assertOk();

    expect($booking->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->latestPayment->fresh()->status)->toBe(PaymentStatus::Voided)
        ->and($wallet->fresh()->balance_cents)->toBe(0)
        ->and(BookingNoShowReport::query()->count())->toBe(1);
});

it('can reset the demo scenario without duplicating its records', function (): void {
    $this->seed(NoShowScenarioSeeder::class);
    $this->seed(NoShowScenarioSeeder::class);

    expect(User::query()->where('email', 'demo.doctor@cure.test')->count())->toBe(1)
        ->and(Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->count())->toBe(1)
        ->and(BookingNoShowReport::query()->count())->toBe(0);
});
