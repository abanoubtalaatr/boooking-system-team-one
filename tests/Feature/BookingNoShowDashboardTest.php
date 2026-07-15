<?php

use App\Enums\BookingStatus;
use App\Enums\NoShowReportStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\NoShowScenarioSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

it('lets the doctor submit a no-show report from the dashboard', function (): void {
    $this->seed(NoShowScenarioSeeder::class);
    $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
    $booking = Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->firstOrFail();

    $this->actingAs($doctor)
        ->get(route('web.doctor.no-show-reports.index'))
        ->assertOk()
        ->assertSee('بلاغات عدم حضور المرضى')
        ->assertSee('BK-NOSHOW-DEMO')
        ->assertSee('المريض لم يحضر');

    $this->actingAs($doctor)
        ->post(route('web.doctor.no-show-reports.store', $booking), [
            'reason' => 'المريض لم يحضر الموعد ولم يستجب لمحاولات التواصل.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $report = BookingNoShowReport::query()->firstOrFail();
    expect($report->doctor_id)->toBe($doctor->id)
        ->and($report->status)->toBe(NoShowReportStatus::PendingReview);

    $this->actingAs($doctor)
        ->get(route('web.doctor.no-show-reports.index'))
        ->assertOk()
        ->assertSee('قيد مراجعة الإدارة')
        ->assertSee($report->reason);
});

it('lets the admin approve a cash report and restore the commission from the dashboard', function (): void {
    Notification::fake();
    $this->seed(NoShowScenarioSeeder::class);
    $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
    $admin = User::query()->where('email', 'demo.admin@cure.test')->firstOrFail();
    $booking = Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->firstOrFail();
    $wallet = Wallet::query()->where('doctor_id', $doctor->id)->firstOrFail();
    $report = BookingNoShowReport::factory()->create([
        'booking_id' => $booking->id,
        'doctor_id' => $doctor->id,
        'reason' => 'المريض لم يحضر الموعد التجريبي.',
    ]);

    $this->actingAs($admin)
        ->get(route('web.admin.no-show-reports.index'))
        ->assertOk()
        ->assertSee('مراجعة بلاغات عدم الحضور')
        ->assertSee('BK-NOSHOW-DEMO')
        ->assertSee('قبول وتسوية');

    $this->actingAs($admin)
        ->patch(route('web.admin.no-show-reports.approve', $report), [
            'review_note' => 'تم التحقق من البلاغ ورد العمولة.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($report->fresh()->status)->toBe(NoShowReportStatus::Approved)
        ->and($booking->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($booking->latestPayment->fresh()->status)->toBe(PaymentStatus::Voided)
        ->and($wallet->fresh()->balance_cents)->toBe(0);
});

it('protects both no-show dashboard pages by role', function (): void {
    $this->seed(NoShowScenarioSeeder::class);
    $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
    $admin = User::query()->where('email', 'demo.admin@cure.test')->firstOrFail();

    $this->get(route('web.doctor.no-show-reports.index'))->assertRedirect(route('login'));
    $this->actingAs($admin)->get(route('web.doctor.no-show-reports.index'))->assertForbidden();
    $this->actingAs($doctor)->get(route('web.admin.no-show-reports.index'))->assertForbidden();
});

it('shows a dashboard error instead of json when a reviewed report is submitted again', function (): void {
    $this->seed(NoShowScenarioSeeder::class);
    $doctor = User::query()->where('email', 'demo.doctor@cure.test')->firstOrFail();
    $booking = Booking::query()->where('booking_number', 'BK-NOSHOW-DEMO')->firstOrFail();
    BookingNoShowReport::factory()->create([
        'booking_id' => $booking->id,
        'doctor_id' => $doctor->id,
    ]);

    $this->actingAs($doctor)
        ->from(route('web.doctor.no-show-reports.index'))
        ->post(route('web.doctor.no-show-reports.store', $booking), [
            'reason' => 'محاولة تكرار نفس البلاغ بعد إرساله سابقًا.',
        ])
        ->assertRedirect(route('web.doctor.no-show-reports.index'))
        ->assertSessionHasErrors('no_show');
});
