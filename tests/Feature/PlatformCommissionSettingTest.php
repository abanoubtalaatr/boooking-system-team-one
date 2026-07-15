<?php

use App\Enums\PaymentMethod;
use App\Models\Setting;
use App\Services\Payments\PlatformCommissionService;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds separate card and cash commission percentages', function (): void {
    $this->seed(SettingSeeder::class);

    expect(Setting::query()->where('key', Setting::PlatformCardCommissionPercentage)->value('value'))
        ->toBe('10.00')
        ->and(Setting::query()->where('key', Setting::PlatformCashCommissionPercentage)->value('value'))
        ->toBe('10.00')
        ->and(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe(1000)
        ->and(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Cash))->toBe(1000);
});

it('reads separate fractional percentages without floating point calculations', function (): void {
    Setting::factory()->platformCardCommission('12.345')->create();
    Setting::factory()->platformCashCommission('7.25')->create();

    expect(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe(1235)
        ->and(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Cash))->toBe(725);
});

it('keeps the commission within zero and one hundred percent', function (string $percentage, int $expected): void {
    Setting::factory()->platformBookingCommission($percentage)->create();

    expect(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe($expected);
})->with([
    'zero percent' => ['0', 0],
    'more than one hundred percent' => ['150', 10000],
]);

it('falls back to configuration when the stored percentage is invalid', function (): void {
    config()->set('payments.platform_commission_bps', 750);
    Setting::factory()->platformBookingCommission('invalid')->create();

    expect(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe(750);
});

it('uses the legacy booking percentage when method settings do not exist', function (): void {
    Setting::factory()->platformBookingCommission('9.50')->create();

    expect(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe(950)
        ->and(app(PlatformCommissionService::class)->bookingCommissionBasisPoints(PaymentMethod::Cash))->toBe(950);
});
