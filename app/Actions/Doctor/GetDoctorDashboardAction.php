<?php

namespace App\Actions\Doctor;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Payments\PlatformCommissionService;
use Illuminate\Database\Eloquent\Collection;

class GetDoctorDashboardAction
{
    public function __construct(private readonly PlatformCommissionService $commission) {}

    /**
     * @return array{
     *     doctor: User,
     *     wallet: array{balance_cents: int, currency: string, payout_blocked: bool, can_withdraw: bool},
     *     current_commission: array{
     *         card: array{basis_points: int, percentage: string},
     *         cash: array{basis_points: int, percentage: string}
     *     },
     *     bookings: array<string, int>,
     *     payments: array<string, int>,
     *     recent_bookings: Collection<int, Booking>,
     *     recent_wallet_transactions: Collection<int, WalletTransaction>
     * }
     */
    public function handle(User $doctor): array
    {
        $doctor->loadMissing(['doctorProfile.specialty', 'doctorProfile.hospital']);
        $wallet = $doctor->wallet()->first();
        $cardCommissionBasisPoints = $this->commission->bookingCommissionBasisPoints(PaymentMethod::Card);
        $cashCommissionBasisPoints = $this->commission->bookingCommissionBasisPoints(PaymentMethod::Cash);

        $recentBookings = Booking::query()
            ->where('doctor_id', $doctor->id)
            ->with([
                'patient:id,name,phone,email',
                'latestPayment' => fn ($query) => $query->select([
                    'payments.id',
                    'payments.uuid',
                    'payments.booking_id',
                    'payments.method',
                    'payments.status',
                    'payments.amount_cents',
                    'payments.currency',
                    'payments.commission_bps',
                    'payments.commission_amount_cents',
                    'payments.doctor_amount_cents',
                    'payments.paid_at',
                    'payments.created_at',
                ]),
            ])
            ->latest()
            ->limit(10)
            ->get();

        $recentWalletTransactions = $wallet
            ? $wallet->transactions()
                ->with([
                    'wallet:id,currency',
                    'payment:id,uuid',
                    'booking:id,booking_number',
                ])
                ->latest()
                ->limit(10)
                ->get()
            : new Collection;

        return [
            'doctor' => $doctor,
            'wallet' => [
                'balance_cents' => (int) ($wallet?->balance_cents ?? 0),
                'currency' => (string) ($wallet?->currency ?? config('services.paymob.currency', 'EGP')),
                'payout_blocked' => (bool) ($wallet?->payout_blocked ?? false),
                'can_withdraw' => $wallet?->canWithdraw() ?? false,
            ],
            'current_commission' => [
                'card' => [
                    'basis_points' => $cardCommissionBasisPoints,
                    'percentage' => $this->commission->formattedPercentage(PaymentMethod::Card),
                ],
                'cash' => [
                    'basis_points' => $cashCommissionBasisPoints,
                    'percentage' => $this->commission->formattedPercentage(PaymentMethod::Cash),
                ],
            ],
            'bookings' => $this->bookingSummary($doctor),
            'payments' => $this->paymentSummary($doctor),
            'recent_bookings' => $recentBookings,
            'recent_wallet_transactions' => $recentWalletTransactions,
        ];
    }

    /** @return array<string, int> */
    private function bookingSummary(User $doctor): array
    {
        $today = now()->toDateString();
        $summary = Booking::query()
            ->where('doctor_id', $doctor->id)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COALESCE(SUM(CASE WHEN booking_date = ? THEN 1 ELSE 0 END), 0) AS today', [$today])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN booking_date >= ? AND status IN (?, ?) THEN 1 ELSE 0 END), 0) AS upcoming',
                [$today, BookingStatus::Confirmed->value, BookingStatus::Rescheduled->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) AS pending',
                [BookingStatus::Pending->value, BookingStatus::PendingPayment->value],
            )
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS confirmed', [BookingStatus::Confirmed->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS completed', [BookingStatus::Completed->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS cancelled', [BookingStatus::Cancelled->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS rejected', [BookingStatus::Rejected->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS expired', [BookingStatus::Expired->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS payment_failed', [BookingStatus::PaymentFailed->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS refund_pending', [BookingStatus::RefundPending->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS refunded', [BookingStatus::Refunded->value])
            ->firstOrFail();

        return collect([
            'total', 'today', 'upcoming', 'pending', 'confirmed', 'completed', 'cancelled',
            'rejected', 'expired', 'payment_failed', 'refund_pending', 'refunded',
        ])->mapWithKeys(fn (string $key): array => [$key => (int) $summary->{$key}])->all();
    }

    /** @return array<string, int> */
    private function paymentSummary(User $doctor): array
    {
        $recognizedStatuses = [
            PaymentStatus::Succeeded->value,
            PaymentStatus::Paid->value,
            PaymentStatus::CashCollected->value,
        ];
        $cardPaidStatuses = [PaymentStatus::Succeeded->value, PaymentStatus::Paid->value];
        $pendingCardStatuses = [
            PaymentStatus::Initiated->value,
            PaymentStatus::Pending->value,
            PaymentStatus::PendingVerification->value,
        ];

        $summary = Payment::query()
            ->where('doctor_id', $doctor->id)
            ->selectRaw('COUNT(*) AS total_transactions')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) AS completed_transactions',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN amount_cents ELSE 0 END), 0) AS gross_revenue_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN commission_amount_cents ELSE 0 END), 0) AS platform_fees_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN doctor_amount_cents ELSE 0 END), 0) AS doctor_net_revenue_cents',
                $recognizedStatuses,
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status IN (?, ?) THEN doctor_amount_cents ELSE 0 END), 0) AS card_net_revenue_cents',
                [PaymentMethod::Card->value, ...$cardPaidStatuses],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status = ? THEN amount_cents ELSE 0 END), 0) AS cash_gross_collected_cents',
                [PaymentMethod::Cash->value, PaymentStatus::CashCollected->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status = ? THEN commission_amount_cents ELSE 0 END), 0) AS cash_commission_cents',
                [PaymentMethod::Cash->value, PaymentStatus::CashCollected->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status IN (?, ?, ?) THEN amount_cents ELSE 0 END), 0) AS pending_card_cents',
                [PaymentMethod::Card->value, ...$pendingCardStatuses],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN method = ? AND status = ? THEN amount_cents ELSE 0 END), 0) AS cash_due_cents',
                [PaymentMethod::Cash->value, PaymentStatus::CashDue->value],
            )
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_cents ELSE 0 END), 0) AS refund_pending_cents', [PaymentStatus::RefundPending->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN amount_cents ELSE 0 END), 0) AS refunded_cents', [PaymentStatus::Refunded->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) AS failed_transactions', [PaymentStatus::Failed->value])
            ->firstOrFail();

        return collect([
            'total_transactions', 'completed_transactions', 'gross_revenue_cents',
            'platform_fees_cents', 'doctor_net_revenue_cents', 'card_net_revenue_cents',
            'cash_gross_collected_cents', 'cash_commission_cents', 'pending_card_cents',
            'cash_due_cents', 'refund_pending_cents', 'refunded_cents', 'failed_transactions',
        ])->mapWithKeys(fn (string $key): array => [$key => (int) $summary->{$key}])->all();
    }
}
