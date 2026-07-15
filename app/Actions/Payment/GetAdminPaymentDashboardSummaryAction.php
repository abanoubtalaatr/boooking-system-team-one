<?php

namespace App\Actions\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;

class GetAdminPaymentDashboardSummaryAction
{
    /**
     * @return array<string, int>
     */
    public function handle(): array
    {
        $recognizedStatuses = [
            PaymentStatus::Succeeded->value,
            PaymentStatus::Paid->value,
            PaymentStatus::CashCollected->value,
        ];
        $pendingStatuses = [
            PaymentStatus::Pending->value,
            PaymentStatus::Initiated->value,
            PaymentStatus::PendingVerification->value,
            PaymentStatus::CashDue->value,
        ];

        $summary = Payment::query()
            ->selectRaw('COUNT(*) AS total_transactions')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN amount_cents ELSE 0 END), 0) AS gross_collected_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN commission_amount_cents ELSE 0 END), 0) AS platform_fees_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN doctor_amount_cents ELSE 0 END), 0) AS doctor_net_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?, ?) THEN 1 ELSE 0 END), 0) AS pending_transactions',
                $pendingStatuses,
            )
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS failed_transactions', [PaymentStatus::Failed->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_cents ELSE 0 END), 0) AS refunded_cents', [PaymentStatus::Refunded->value])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status IN (?, ?) THEN amount_cents ELSE 0 END), 0) AS card_collected_cents',
                [PaymentMethod::Card->value, PaymentStatus::Succeeded->value, PaymentStatus::Paid->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status = ? THEN amount_cents ELSE 0 END), 0) AS cash_collected_cents',
                [PaymentMethod::Cash->value, PaymentStatus::CashCollected->value],
            )
            ->firstOrFail();

        return collect([
            'total_transactions',
            'gross_collected_cents',
            'platform_fees_cents',
            'doctor_net_cents',
            'pending_transactions',
            'failed_transactions',
            'refunded_cents',
            'card_collected_cents',
            'cash_collected_cents',
        ])->mapWithKeys(fn (string $key): array => [$key => (int) $summary->{$key}])->all();
    }
}
