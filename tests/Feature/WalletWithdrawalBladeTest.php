<?php

use App\Enums\WalletTransactionType;
use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createWithdrawalWallet(User $doctor, int $balanceCents = 100000): Wallet
{
    return Wallet::factory()->create([
        'doctor_id' => $doctor->id,
        'balance_cents' => $balanceCents,
        'currency' => 'EGP',
        'payout_blocked' => false,
    ]);
}

function createPendingWithdrawal(User $doctor, Wallet $wallet, int $amountCents): WalletWithdrawal
{
    return WalletWithdrawal::factory()->create([
        'doctor_id' => $doctor->id,
        'wallet_id' => $wallet->id,
        'amount_cents' => $amountCents,
        'currency' => $wallet->currency,
        'status' => WalletWithdrawalStatus::PendingReview,
    ]);
}

test('wallet withdrawal pages require authentication and the correct role', function () {
    $this->get('/doctor/wallet')->assertRedirect(route('login'));
    $this->get('/admin/withdrawals')->assertRedirect(route('login'));

    $doctor = User::factory()->create(['role' => 'doctor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($doctor)->get('/admin/withdrawals')->assertForbidden();
    $this->actingAs($admin)->get('/doctor/wallet')->assertForbidden();
});

test('doctor wallet page shows available balance after pending requests and only own history', function () {
    $doctor = User::factory()->create(['role' => 'doctor', 'name' => 'طبيب المحفظة']);
    $otherDoctor = User::factory()->create(['role' => 'doctor', 'name' => 'طبيب آخر']);
    $wallet = createWithdrawalWallet($doctor, 100000);
    $otherWallet = createWithdrawalWallet($otherDoctor, 200000);

    createPendingWithdrawal($doctor, $wallet, 30000);
    createPendingWithdrawal($otherDoctor, $otherWallet, 150000);

    $this->actingAs($doctor)
        ->get('/doctor/wallet')
        ->assertOk()
        ->assertViewIs('doctor.wallet')
        ->assertViewHas('available_cents', 70000)
        ->assertViewHas('withdrawals', fn ($withdrawals): bool => $withdrawals->total() === 1
            && $withdrawals->first()->doctor_id === $doctor->id)
        ->assertSeeText('700.00 EGP')
        ->assertDontSeeText('طبيب آخر');
});

test('doctor creates a pending withdrawal without immediately deducting wallet balance', function () {
    $doctor = User::factory()->create(['role' => 'doctor']);
    $wallet = createWithdrawalWallet($doctor, 100000);
    $idempotencyKey = (string) Str::uuid();

    $this->actingAs($doctor)
        ->post('/doctor/wallet/withdrawals', [
            'amount' => '600.00',
            'idempotency_key' => $idempotencyKey,
        ])
        ->assertRedirect(route('web.doctor.wallet.index'))
        ->assertSessionHas('success');

    expect($wallet->fresh()->balance_cents)->toBe(100000);
    $this->assertDatabaseHas('wallet_withdrawals', [
        'doctor_id' => $doctor->id,
        'amount_cents' => 60000,
        'status' => WalletWithdrawalStatus::PendingReview->value,
        'idempotency_key' => $idempotencyKey,
    ]);
});

test('pending requests prevent a doctor from requesting more than available balance', function () {
    $doctor = User::factory()->create(['role' => 'doctor']);
    $wallet = createWithdrawalWallet($doctor, 100000);
    createPendingWithdrawal($doctor, $wallet, 60000);

    $this->actingAs($doctor)
        ->from('/doctor/wallet')
        ->post('/doctor/wallet/withdrawals', [
            'amount' => '500.00',
            'idempotency_key' => (string) Str::uuid(),
        ])
        ->assertRedirect('/doctor/wallet')
        ->assertSessionHasErrors('amount');

    expect($wallet->fresh()->balance_cents)->toBe(100000);
    expect(WalletWithdrawal::query()->where('doctor_id', $doctor->id)->count())->toBe(1);
});

test('admin sees withdrawal requests for all doctors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $firstDoctor = User::factory()->create(['role' => 'doctor', 'name' => 'الطبيب الأول']);
    $secondDoctor = User::factory()->create(['role' => 'doctor', 'name' => 'الطبيب الثاني']);
    createPendingWithdrawal($firstDoctor, createWithdrawalWallet($firstDoctor), 20000);
    createPendingWithdrawal($secondDoctor, createWithdrawalWallet($secondDoctor), 30000);

    $this->actingAs($admin)
        ->get('/admin/withdrawals')
        ->assertOk()
        ->assertViewIs('admin.withdrawals')
        ->assertViewHas('withdrawals', fn ($withdrawals): bool => $withdrawals->total() === 2)
        ->assertSeeText('الطبيب الأول')
        ->assertSeeText('الطبيب الثاني');
});

test('admin approval deducts wallet once and creates one debit transaction', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $doctor = User::factory()->create(['role' => 'doctor']);
    $wallet = createWithdrawalWallet($doctor, 100000);
    $withdrawal = createPendingWithdrawal($doctor, $wallet, 40000);
    $url = route('web.admin.withdrawals.complete', $withdrawal);

    $this->actingAs($admin)->patch($url)->assertRedirect()->assertSessionHas('success');
    $this->actingAs($admin)->patch($url)->assertRedirect()->assertSessionHas('success');

    $withdrawal->refresh();
    expect($wallet->fresh()->balance_cents)->toBe(60000)
        ->and($withdrawal->status)->toBe(WalletWithdrawalStatus::Completed)
        ->and($withdrawal->reviewed_by)->toBe($admin->id)
        ->and($withdrawal->balance_before_cents)->toBe(100000)
        ->and($withdrawal->balance_after_cents)->toBe(60000);

    $transactions = WalletTransaction::query()->where('wallet_id', $wallet->id)->get();
    expect($transactions)->toHaveCount(1)
        ->and($transactions->first()->type)->toBe(WalletTransactionType::WithdrawalDebit)
        ->and($transactions->first()->amount_cents)->toBe(-40000)
        ->and($transactions->first()->balance_after_cents)->toBe(60000);
});

test('admin cancellation keeps wallet balance and stores rejection reason', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $doctor = User::factory()->create(['role' => 'doctor']);
    $wallet = createWithdrawalWallet($doctor, 100000);
    $withdrawal = createPendingWithdrawal($doctor, $wallet, 40000);

    $this->actingAs($admin)
        ->patch(route('web.admin.withdrawals.cancel', $withdrawal), [
            'rejection_reason' => 'بيانات التحويل غير مكتملة',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $withdrawal->refresh();
    expect($wallet->fresh()->balance_cents)->toBe(100000)
        ->and($withdrawal->status)->toBe(WalletWithdrawalStatus::Cancelled)
        ->and($withdrawal->rejection_reason)->toBe('بيانات التحويل غير مكتملة')
        ->and(WalletTransaction::query()->where('wallet_id', $wallet->id)->count())->toBe(0);
});
