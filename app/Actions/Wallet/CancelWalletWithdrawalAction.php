<?php

namespace App\Actions\Wallet;

use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelWalletWithdrawalAction
{
    public function handle(WalletWithdrawal $withdrawal, User $admin, string $reason): WalletWithdrawal
    {
        return DB::transaction(function () use ($withdrawal, $admin, $reason): WalletWithdrawal {
            Wallet::query()->lockForUpdate()->findOrFail($withdrawal->wallet_id);
            $lockedWithdrawal = WalletWithdrawal::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if ($lockedWithdrawal->status === WalletWithdrawalStatus::Cancelled) {
                return $lockedWithdrawal;
            }

            if ($lockedWithdrawal->status !== WalletWithdrawalStatus::PendingReview) {
                throw ValidationException::withMessages(['withdrawal' => 'لا يمكن إلغاء طلب سحب مكتمل.']);
            }

            $lockedWithdrawal->update([
                'status' => WalletWithdrawalStatus::Cancelled,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $lockedWithdrawal->fresh(['doctor', 'reviewer', 'wallet']);
        }, attempts: 3);
    }
}
