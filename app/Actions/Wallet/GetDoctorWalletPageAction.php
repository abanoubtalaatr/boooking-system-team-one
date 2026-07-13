<?php

namespace App\Actions\Wallet;

use App\Enums\WalletWithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetDoctorWalletPageAction
{
    /**
     * @return array{
     *     wallet: Wallet,
     *     balance_cents: int,
     *     pending_cents: int,
     *     available_cents: int,
     *     completed_cents: int,
     *     withdrawals: LengthAwarePaginator<int, WalletWithdrawal>
     * }
     */
    public function handle(User $doctor): array
    {
        $wallet = Wallet::query()->firstOrCreate(
            ['doctor_id' => $doctor->id, 'currency' => config('services.paymob.currency', 'EGP')],
            ['balance_cents' => 0, 'payout_blocked' => false],
        );
        $pendingCents = (int) $wallet->withdrawals()
            ->where('status', WalletWithdrawalStatus::PendingReview)
            ->sum('amount_cents');
        $completedCents = (int) $wallet->withdrawals()
            ->where('status', WalletWithdrawalStatus::Completed)
            ->sum('amount_cents');

        return [
            'wallet' => $wallet,
            'balance_cents' => (int) $wallet->balance_cents,
            'pending_cents' => $pendingCents,
            'available_cents' => max(0, (int) $wallet->balance_cents - $pendingCents),
            'completed_cents' => $completedCents,
            'withdrawals' => $wallet->withdrawals()
                ->with('reviewer:id,name')
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ];
    }
}
