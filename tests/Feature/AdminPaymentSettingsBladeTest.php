<?php

use App\Enums\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PlatformCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payment commission settings page requires an authenticated admin', function () {
    $this->get('/admin/settings/payments')->assertRedirect(route('login'));

    $doctor = User::factory()->create(['role' => 'doctor']);
    $this->actingAs($doctor)->get('/admin/settings/payments')->assertForbidden();
    $this->actingAs($doctor)->put('/admin/settings/payments', [
        'card_commission_percentage' => '10.00',
        'cash_commission_percentage' => '10.00',
    ])->assertForbidden();
});

test('admin sees and updates separate card and cash commission settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Setting::factory()->platformCardCommission('8.50')->create();
    Setting::factory()->platformCashCommission('12.25')->create();

    $this->actingAs($admin)
        ->get('/admin/settings/payments')
        ->assertOk()
        ->assertViewIs('admin.payment-settings')
        ->assertSeeText('عمولة الدفع بالفيزا')
        ->assertSeeText('عمولة الدفع كاش')
        ->assertSee('value="8.50"', false)
        ->assertSee('value="12.25"', false);

    $this->actingAs($admin)
        ->put('/admin/settings/payments', [
            'card_commission_percentage' => '7.75',
            'cash_commission_percentage' => '14.50',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $commission = app(PlatformCommissionService::class);
    expect($commission->bookingCommissionBasisPoints(PaymentMethod::Card))->toBe(775)
        ->and($commission->bookingCommissionBasisPoints(PaymentMethod::Cash))->toBe(1450)
        ->and(Setting::query()->where('key', Setting::PlatformCardCommissionPercentage)->value('value'))->toBe('7.75')
        ->and(Setting::query()->where('key', Setting::PlatformCashCommissionPercentage)->value('value'))->toBe('14.50');
});

test('commission percentages must be between zero and one hundred with at most two decimals', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->from('/admin/settings/payments')
        ->put('/admin/settings/payments', [
            'card_commission_percentage' => '100.01',
            'cash_commission_percentage' => '9.999',
        ])
        ->assertRedirect('/admin/settings/payments')
        ->assertSessionHasErrors(['card_commission_percentage', 'cash_commission_percentage']);
});
