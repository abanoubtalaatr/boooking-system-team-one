<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\WalletTransactionType;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\WalletTransaction;
use App\Notifications\PaymentRefundedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefundService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly WalletService $wallets,
    ) {}

    public function refundFull(Payment $payment, string $reason): PaymentRefund
    {
        $idempotencyKey = "refund:{$payment->uuid}:full";
        $refund = PaymentRefund::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'uuid' => (string) Str::uuid(),
                'payment_id' => $payment->id,
                'amount_cents' => $payment->amount_cents,
                'status' => RefundStatus::Pending,
                'reason' => $reason,
                'requested_at' => now(),
            ],
        );

        if ($refund->status === RefundStatus::Succeeded) {
            return $refund;
        }

        if ($refund->status === RefundStatus::Failed) {
            $refund->update([
                'status' => RefundStatus::Pending,
                'failure_message' => null,
                'failed_at' => null,
                'requested_at' => now(),
            ]);
        }

        $payment->update(['status' => PaymentStatus::RefundPending]);
        $payment->booking()->update([
            'status' => BookingStatus::RefundPending,
            'payment_status' => PaymentStatus::RefundPending,
        ]);

        $result = $this->gateway->refund($payment, $payment->amount_cents);

        if (! $result->succeeded) {
            $refund->update([
                'status' => RefundStatus::Failed,
                'failure_message' => $result->failureMessage,
                'failed_at' => now(),
            ]);

            return $refund->refresh();
        }

        DB::transaction(function () use ($payment, $refund, $result): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $refund->update([
                'status' => RefundStatus::Succeeded,
                'provider_refund_id' => $result->providerRefundId,
                'completed_at' => now(),
            ]);
            $lockedPayment->update([
                'status' => PaymentStatus::Refunded,
                'refunded_at' => now(),
            ]);
            $lockedPayment->booking()->update([
                'status' => BookingStatus::Refunded,
                'payment_status' => PaymentStatus::Refunded,
                'hold_expires_at' => null,
            ]);

            if (WalletTransaction::query()->where('idempotency_key', "card-credit:{$lockedPayment->uuid}")->exists()) {
                $this->wallets->record(
                    $lockedPayment,
                    WalletTransactionType::RefundDebit,
                    -$lockedPayment->doctor_amount_cents,
                    "refund-debit:{$lockedPayment->uuid}",
                );
            }

            $lockedPayment->booking->slot()->where('reserved_booking_id', $lockedPayment->booking_id)->update([
                'is_booked' => false,
                'reservation_status' => SlotReservationStatus::Available,
                'reserved_booking_id' => null,
                'reserved_until' => null,
            ]);
            $notification = (new PaymentRefundedNotification($lockedPayment->booking, $lockedPayment, $refund))->afterCommit();
            $lockedPayment->booking->patient->notify($notification);
            $lockedPayment->booking->doctor->notify((new PaymentRefundedNotification($lockedPayment->booking, $lockedPayment, $refund))->afterCommit());
        }, attempts: 3);

        return $refund->refresh();
    }
}
