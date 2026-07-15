<?php

namespace App\Actions\Wallet;

use App\Enums\WalletTransactionType;
use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteWalletWithdrawalAction
{
    public function handle(WalletWithdrawal $withdrawal, User $admin): WalletWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin): WalletWithdrawal {
            $wallet = Wallet::query()->lockForUpdate()->findOrFail($withdrawal->wallet_id);
            $lockedWithdrawal = WalletWithdrawal::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if ($lockedWithdrawal->status === WalletWithdrawalStatus::Completed) {
                return $lockedWithdrawal;
            }

            if ($lockedWithdrawal->status !== WalletWithdrawalStatus::PendingReview) {
                throw ValidationException::withMessages(['withdrawal' => 'لا يمكن قبول طلب سحب ملغي.']);
            }

            if ((int) $wallet->balance_cents < (int) $lockedWithdrawal->amount_cents) {
                throw ValidationException::withMessages(['withdrawal' => 'رصيد المحفظة الحالي لا يكفي لإتمام السحب.']);
            }

            $balanceBeforeCents = (int) $wallet->balance_cents;
            $wallet->decrement('balance_cents', $lockedWithdrawal->amount_cents);
            $wallet->refresh();
            $wallet->update(['payout_blocked' => $wallet->balance_cents <= 0]);

            WalletTransaction::query()->firstOrCreate(
                ['idempotency_key' => "withdrawal:{$lockedWithdrawal->uuid}"],
                [
                    'wallet_id' => $wallet->id,
                    'type' => WalletTransactionType::WithdrawalDebit,
                    'amount_cents' => -$lockedWithdrawal->amount_cents,
                    'balance_after_cents' => $wallet->balance_cents,
                    'metadata' => [
                        'withdrawal_uuid' => $lockedWithdrawal->uuid,
                        'reviewed_by' => $admin->id,
                    ],
                ],
            );

            $lockedWithdrawal->update([
                'status' => WalletWithdrawalStatus::Completed,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
                'balance_before_cents' => $balanceBeforeCents,
                'balance_after_cents' => $wallet->balance_cents,
            ]);

            return $lockedWithdrawal->fresh(['doctor', 'reviewer', 'wallet']);
        }, attempts: 3);
    }
}
