<?php

use App\Enums\NoShowReportStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletWithdrawalStatus;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\Conversation;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use App\Models\WalletWithdrawal;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

it('seeds a complete and repeatable platform demo', function (): void {
    $this->seed(DatabaseSeeder::class);

    $superAdmin = User::query()->findOrFail(1);
    $regularAdmin = User::query()->where('email', 'admin@cure.test')->firstOrFail();

    expect($superAdmin->email)->toBe('camila.herman@example.net')
        ->and($superAdmin->hasRole('super-admin'))->toBeTrue()
        ->and($superAdmin->getAllPermissions())->toHaveCount(Permission::query()->count())
        ->and($regularAdmin->hasRole('admin'))->toBeTrue()
        ->and($regularAdmin->permissions)->toHaveCount(Permission::query()->count())
        ->and(User::role('doctor')->where('email', 'like', 'doctor%@cure.test')->count())->toBe(6)
        ->and(Patient::query()->where('email', 'like', 'patient%@cure.test')->count())->toBe(8)
        ->and(Booking::query()->where('booking_number', 'like', 'BK-DEMO-%')->count())->toBe(60)
        ->and(Payment::query()->where('uuid', 'like', '20000000-%')->count())->toBe(60)
        ->and(Payment::query()->where('method', PaymentMethod::Card)->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Paid])->exists())->toBeTrue()
        ->and(Payment::query()->where('method', PaymentMethod::Cash)->where('status', PaymentStatus::CashCollected)->exists())->toBeTrue()
        ->and(Payment::query()->where('status', PaymentStatus::Pending)->exists())->toBeTrue()
        ->and(Payment::query()->where('status', PaymentStatus::Failed)->exists())->toBeTrue()
        ->and(Payment::query()->where('status', PaymentStatus::Refunded)->exists())->toBeTrue()
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::PendingReview)->exists())->toBeTrue()
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::Approved)->exists())->toBeTrue()
        ->and(BookingNoShowReport::query()->where('status', NoShowReportStatus::Rejected)->exists())->toBeTrue()
        ->and(WalletWithdrawal::query()->where('status', WalletWithdrawalStatus::PendingReview)->exists())->toBeTrue()
        ->and(WalletWithdrawal::query()->where('status', WalletWithdrawalStatus::Completed)->exists())->toBeTrue()
        ->and(WalletWithdrawal::query()->where('status', WalletWithdrawalStatus::Cancelled)->exists())->toBeTrue()
        ->and(Review::query()->count())->toBeGreaterThanOrEqual(8)
        ->and(Conversation::query()->count())->toBeGreaterThanOrEqual(8);

    $demoCounts = [
        Booking::query()->where('booking_number', 'like', 'BK-DEMO-%')->count(),
        Payment::query()->where('uuid', 'like', '20000000-%')->count(),
        BookingNoShowReport::query()->count(),
        WalletWithdrawal::query()->where('uuid', 'like', '30000000-%')->count(),
    ];

    $this->seed(DatabaseSeeder::class);

    expect([
        Booking::query()->where('booking_number', 'like', 'BK-DEMO-%')->count(),
        Payment::query()->where('uuid', 'like', '20000000-%')->count(),
        BookingNoShowReport::query()->count(),
        WalletWithdrawal::query()->where('uuid', 'like', '30000000-%')->count(),
    ])->toBe($demoCounts);
});
