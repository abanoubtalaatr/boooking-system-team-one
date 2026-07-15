<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\NoShowReportStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\NoShowReportDomainException;
use App\Models\Booking;
use App\Models\BookingNoShowReport;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\BookingNoShowReportReviewedNotification;
use App\Services\Payments\RefundService;
use App\Services\Payments\WalletService;
use Illuminate\Support\Facades\DB;

class ApproveBookingNoShowReportAction
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly RefundService $refunds,
    ) {}

    public function handle(BookingNoShowReport $report, User $admin, ?string $note): BookingNoShowReport
    {
        $paymentToRefund = DB::transaction(function () use ($report, $admin, $note): ?Payment {
            $lockedReport = BookingNoShowReport::query()->lockForUpdate()->findOrFail($report->id);

            if ($lockedReport->status !== NoShowReportStatus::PendingReview) {
                throw new NoShowReportDomainException('تمت مراجعة هذا البلاغ من قبل.', 'report_already_reviewed', 409);
            }

            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedReport->booking_id);
            $payment = Payment::query()
                ->where('booking_id', $booking->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($payment?->method === PaymentMethod::Card
                && ! in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::Paid], true)) {
                throw new NoShowReportDomainException('حالة الدفع الإلكتروني لا تسمح برد المبلغ.', 'payment_not_refundable', 409);
            }

            $lockedReport->update([
                'status' => NoShowReportStatus::Approved,
                'reviewed_by' => $admin->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);

            if ($payment?->method === PaymentMethod::Card) {
                return $payment;
            }

            if ($payment?->method === PaymentMethod::Cash) {
                if ($payment->status === PaymentStatus::CashCollected
                    && WalletTransaction::query()->where('idempotency_key', "cash-commission:{$payment->uuid}")->exists()) {
                    $this->wallets->record(
                        $payment,
                        WalletTransactionType::Adjustment,
                        $payment->commission_amount_cents,
                        "no-show-commission-reversal:{$payment->uuid}",
                        ['reason' => 'approved_no_show_report', 'report_id' => $lockedReport->id],
                    );
                }

                $payment->update(['status' => PaymentStatus::Voided]);
            }

            $booking->update([
                'status' => BookingStatus::Cancelled,
                'payment_status' => $payment ? PaymentStatus::Voided : $booking->payment_status,
                'hold_expires_at' => null,
            ]);
            $booking->slot()->where('reserved_booking_id', $booking->id)->update([
                'is_booked' => false,
                'reservation_status' => SlotReservationStatus::Available,
                'reserved_booking_id' => null,
                'reserved_until' => null,
            ]);

            return null;
        }, attempts: 3);

        if ($paymentToRefund) {
            $this->refunds->refundFull($paymentToRefund, 'approved_no_show_report');
        }

        $freshReport = $report->fresh(['booking', 'doctor', 'reviewer']);
        $freshReport->doctor->notify(
            (new BookingNoShowReportReviewedNotification($freshReport))->afterCommit(),
        );

        return $freshReport;
    }
}
