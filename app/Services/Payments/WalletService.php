<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\PaymentDomainException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\PaymentSucceededNotification;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function markCashCollected(Booking $booking, int $doctorId): Payment
    {
        return DB::transaction(function () use ($booking, $doctorId): Payment {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ((int) $lockedBooking->doctor_id !== $doctorId) {
                throw new PaymentDomainException('غير مسموح بتحديث هذا الحجز.', 'forbidden', 403);
            }

            $payment = Payment::query()
                ->where('booking_id', $lockedBooking->id)
                ->where('method', PaymentMethod::Cash)
                ->lockForUpdate()
                ->latest('id')
                ->firstOrFail();

            if ($payment->status === PaymentStatus::CashCollected) {
                return $payment;
            }

            if ($payment->status !== PaymentStatus::CashDue) {
                throw new PaymentDomainException('عملية الدفع النقدي ليست مستحقة.', 'cash_not_due', 409);
            }

            $payment->update([
                'status' => PaymentStatus::CashCollected,
                'paid_at' => now(),
            ]);
            $lockedBooking->update(['payment_status' => PaymentStatus::CashCollected]);
            $this->record(
                $payment,
                WalletTransactionType::CashCommissionDebit,
                -$payment->commission_amount_cents,
                "cash-commission:{$payment->uuid}",
            );
            $lockedBooking->patient->notify((new PaymentSucceededNotification($lockedBooking, $payment))->afterCommit());
            $lockedBooking->doctor->notify((new PaymentSucceededNotification($lockedBooking, $payment))->afterCommit());

            return $payment->fresh('booking');
        }, attempts: 3);
    }

    public function record(
        Payment $payment,
        WalletTransactionType $type,
        int $amountCents,
        string $idempotencyKey,
        array $metadata = [],
    ): ?WalletTransaction {
        if ($amountCents === 0) {
            return null;
        }

        return DB::transaction(function () use ($payment, $type, $amountCents, $idempotencyKey, $metadata): WalletTransaction {
            $existing = WalletTransaction::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $existing;
            }

            Wallet::query()->firstOrCreate(
                ['doctor_id' => $payment->doctor_id, 'currency' => $payment->currency],
                ['balance_cents' => 0, 'payout_blocked' => false],
            );
            $wallet = Wallet::query()
                ->where('doctor_id', $payment->doctor_id)
                ->where('currency', $payment->currency)
                ->lockForUpdate()
                ->firstOrFail();
            $wallet->increment('balance_cents', $amountCents);
            $wallet->refresh();
            $wallet->update(['payout_blocked' => $wallet->balance_cents <= 0]);

            return WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'type' => $type,
                'amount_cents' => $amountCents,
                'balance_after_cents' => $wallet->balance_cents,
                'idempotency_key' => $idempotencyKey,
                'metadata' => $metadata ?: null,
            ]);
        }, attempts: 3);
    }
}
