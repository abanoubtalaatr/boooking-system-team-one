<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Admin\ReportsDashboard;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\Reports\AdminReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow('2026-07-16 12:00:00');

    $this->admin = User::factory()->restrictedAdmin()->create();
    $this->admin->givePermissionTo('reports.view');
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('shows an interactive report with all requested periods and charts', function (): void {
    $booking = Booking::factory()->create(['created_at' => now()->subDay()]);
    Payment::factory()->create([
        'booking_id' => $booking->id,
        'patient_id' => $booking->patient_id,
        'doctor_id' => $booking->doctor_id,
        'method' => PaymentMethod::Card,
        'status' => PaymentStatus::Succeeded,
        'commission_amount_cents' => 5000,
        'created_at' => now()->subDay(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.reports'))
        ->assertSuccessful()
        ->assertSee('7 أيام')
        ->assertSee('40 يومًا')
        ->assertSee('12 شهرًا')
        ->assertSee('معدل الحجوزات')
        ->assertSee('الفيزا مقابل الكاش')
        ->assertSee('أرباح المنصة')
        ->assertSee('50.00 EGP')
        ->assertSeeHtml('data-chart="bookings-rate"')
        ->assertSeeHtml('data-chart="payment-methods"')
        ->assertSeeHtml('data-chart="platform-profit"');
});

it('aggregates settled payment methods and excludes pending commission from profit', function (): void {
    $cardBooking = Booking::factory()->create(['created_at' => now()->subDay()]);
    $cashBooking = Booking::factory()->create(['created_at' => now()->subDays(2)]);
    $pendingBooking = Booking::factory()->create(['created_at' => now()->subDays(3)]);

    Payment::factory()->create([
        'booking_id' => $cardBooking->id,
        'patient_id' => $cardBooking->patient_id,
        'doctor_id' => $cardBooking->doctor_id,
        'method' => PaymentMethod::Card,
        'status' => PaymentStatus::Succeeded,
        'commission_amount_cents' => 5000,
        'created_at' => now()->subDay(),
    ]);
    Payment::factory()->create([
        'booking_id' => $cashBooking->id,
        'patient_id' => $cashBooking->patient_id,
        'doctor_id' => $cashBooking->doctor_id,
        'method' => PaymentMethod::Cash,
        'status' => PaymentStatus::CashCollected,
        'commission_amount_cents' => 2500,
        'created_at' => now()->subDays(2),
    ]);
    Payment::factory()->create([
        'booking_id' => $pendingBooking->id,
        'patient_id' => $pendingBooking->patient_id,
        'doctor_id' => $pendingBooking->doctor_id,
        'method' => PaymentMethod::Card,
        'status' => PaymentStatus::Pending,
        'commission_amount_cents' => 9000,
        'created_at' => now()->subDays(3),
    ]);

    $report = app(AdminReportService::class)->build('week');

    expect($report['bookings'])->toHaveCount(7)
        ->and($report['summary']['bookings'])->toBe(3)
        ->and($report['summary']['paid_bookings'])->toBe(2)
        ->and($report['summary']['profit_cents'])->toBe(7500)
        ->and(collect($report['payments'])->sum('card'))->toBe(1)
        ->and(collect($report['payments'])->sum('cash'))->toBe(1);
});

it('switches between forty daily points and twelve monthly points', function (): void {
    Livewire::actingAs($this->admin)
        ->test(ReportsDashboard::class)
        ->call('setPeriod', 'forty_days')
        ->assertSet('period', 'forty_days')
        ->assertSee('آخر 40 يومًا')
        ->call('setPeriod', 'year')
        ->assertSet('period', 'year')
        ->assertSee('آخر 12 شهرًا');

    expect(app(AdminReportService::class)->build('forty_days')['bookings'])->toHaveCount(40)
        ->and(app(AdminReportService::class)->build('year')['bookings'])->toHaveCount(12);
});

it('forbids admins without the reports permission', function (): void {
    $admin = User::factory()->restrictedAdmin()->create();

    $this->actingAs($admin)->get(route('admin.reports'))->assertForbidden();
});
