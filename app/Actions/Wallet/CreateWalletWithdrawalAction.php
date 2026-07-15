<?php

namespace App\Actions\Wallet;

use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use App\Services\Payments\MoneyCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateWalletWithdrawalAction
{
    public function __construct(private readonly MoneyCalculator $money) {}

    public function handle(User $doctor, string $amount, string $idempotencyKey): WalletWithdrawal
    {
        $amountCents = $this->money->decimalToCents($amount);

        return DB::transaction(function () use ($doctor, $amountCents, $idempotencyKey): WalletWithdrawal {
            $existing = WalletWithdrawal::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                if ((int) $existing->doctor_id !== (int) $doctor->id) {
                    throw ValidationException::withMessages(['amount' => 'تعذر استخدام مفتاح الطلب.']);
                }

                return $existing;
            }

            Wallet::query()->firstOrCreate(
                ['doctor_id' => $doctor->id, 'currency' => config('services.paymob.currency', 'EGP')],
                ['balance_cents' => 0, 'payout_blocked' => false],
            );
            $wallet = Wallet::query()
                ->where('doctor_id', $doctor->id)
                ->where('currency', config('services.paymob.currency', 'EGP'))
                ->lockForUpdate()
                ->firstOrFail();
            $pendingCents = (int) $wallet->withdrawals()
                ->where('status', WalletWithdrawalStatus::PendingReview)
                ->sum('amount_cents');
            $availableCents = max(0, (int) $wallet->balance_cents - $pendingCents);

            if ($wallet->payout_blocked || $amountCents > $availableCents) {
                throw ValidationException::withMessages([
                    'amount' => 'المبلغ المطلوب أكبر من الرصيد المتاح للسحب.',
                ]);
            }

            return WalletWithdrawal::query()->create([
                'uuid' => (string) Str::uuid(),
                'doctor_id' => $doctor->id,
                'wallet_id' => $wallet->id,
                'amount_cents' => $amountCents,
                'currency' => $wallet->currency,
                'status' => WalletWithdrawalStatus::PendingReview,
                'idempotency_key' => $idempotencyKey,
            ]);
        }, attempts: 3);
    }
}
