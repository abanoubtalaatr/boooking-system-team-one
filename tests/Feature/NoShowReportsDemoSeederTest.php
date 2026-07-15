<?php

use App\Enums\NoShowReportStatus;
use App\Models\BookingNoShowReport;
use App\Models\User;
use Database\Seeders\NoShowReportsDemoSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('seeds three idempotent reports that appear on the admin dashboard', function (): void {
    $this->seed(NoShowReportsDemoSeeder::class);
    $this->seed(NoShowReportsDemoSeeder::class);

    $admin = User::query()->where('email', 'demo.admin@cure.test')->firstOrFail();

    expect(BookingNoShowReport::query()->count())->toBe(3)
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::PendingReview)->count())->toBe(1)
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::Approved)->count())->toBe(1)
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::Rejected)->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('web.admin.no-show-reports.index'))
        ->assertOk()
        ->assertSee('BK-NOSHOW-DEMO')
        ->assertSee('BK-NOSHOW-APPROVED')
        ->assertSee('BK-NOSHOW-REJECTED')
        ->assertSee('قبول وتسوية')
        ->assertSee('تم رفض البلاغ لعدم كفاية دليل التواصل مع المريض.');
});
