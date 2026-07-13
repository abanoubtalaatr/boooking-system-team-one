<?php

namespace App\Actions\Wallet;

use App\Enums\WalletWithdrawalStatus;
use App\Models\WalletWithdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminWithdrawalsAction
{
    /**
     * @param  array{status?: string|null, doctor_id?: int|null, per_page?: int|null}  $filters
     * @return array{
     *     withdrawals: LengthAwarePaginator<int, WalletWithdrawal>,
     *     summary: array{pending_count: int, pending_cents: int, completed_cents: int, cancelled_count: int}
     * }
     */
    public function handle(array $filters): array
    {
        $withdrawals = WalletWithdrawal::query()
            ->with(['doctor:id,name,email', 'reviewer:id,name', 'wallet:id,currency'])
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['doctor_id']), fn ($query) => $query->where('doctor_id', $filters['doctor_id']))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $summary = WalletWithdrawal::query()
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS pending_count', [WalletWithdrawalStatus::PendingReview->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_cents ELSE 0 END), 0) AS pending_cents', [WalletWithdrawalStatus::PendingReview->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_cents ELSE 0 END), 0) AS completed_cents', [WalletWithdrawalStatus::Completed->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS cancelled_count', [WalletWithdrawalStatus::Cancelled->value])
            ->firstOrFail();

        return [
            'withdrawals' => $withdrawals,
            'summary' => [
                'pending_count' => (int) $summary->pending_count,
                'pending_cents' => (int) $summary->pending_cents,
                'completed_cents' => (int) $summary->completed_cents,
                'cancelled_count' => (int) $summary->cancelled_count,
            ],
        ];
    }
}
