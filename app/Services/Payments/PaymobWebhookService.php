<?php

namespace App\Services\Payments;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Enums\WalletTransactionType;
use App\Exceptions\PaymentDomainException;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentSucceededNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymobWebhookService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly RefundService $refunds,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(array $payload): void
    {
        $reference = (string) (Arr::get($payload, 'special_reference')
            ?? Arr::get($payload, 'order.merchant_order_id')
            ?? Arr::get($payload, 'payment_key_claims.extra.special_reference')
            ?? '');
        $transactionId = (string) Arr::get($payload, 'id', '');

        $payment = Payment::query()
            ->when($reference !== '', fn ($query) => $query->where('uuid', $reference))
            ->when($reference === '', fn ($query) => $query->where('provider_order_id', (string) Arr::get($payload, 'order.id', '')))
            ->first();

        if (! $payment) {
            throw new PaymentDomainException('Payment reference not found.', 'payment_reference_not_found', 404);
        }

        $this->validateTransaction($payment, $payload, $reference);

        if ($payment->provider_transaction_id === $transactionId
            && in_array($payment->status, [PaymentStatus::Succeeded, PaymentStatus::RefundPending, PaymentStatus::Refunded], true)) {
            return;
        }

        if ((bool) Arr::get($payload, 'pending', false)) {
            $payment->update([
                'status' => PaymentStatus::Pending,
                'provider_transaction_id' => $transactionId ?: null,
            ]);

            return;
        }

        if ((bool) Arr::get($payload, 'success', false) && ! (bool) Arr::get($payload, 'error_occured', false)) {
            $this->handleSuccess($payment, $transactionId);

            return;
        }

        $this->handleFailure($payment, $transactionId, $payload);
    }

    private function handleSuccess(Payment $payment, string $transactionId): void
    {
        $mustRefund = DB::transaction(function () use ($payment, $transactionId): bool {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->provider_transaction_id === $transactionId
                && in_array($lockedPayment->status, [PaymentStatus::Succeeded, PaymentStatus::RefundPending, PaymentStatus::Refunded], true)) {
                return false;
            }

            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);
            $slot = AvailabilitySlot::query()->lockForUpdate()->findOrFail($booking->availability_slot_id);

            $lockedPayment->update([
                'status' => PaymentStatus::Succeeded,
                'provider_transaction_id' => $transactionId,
                'paid_at' => now(),
                'failure_code' => null,
                'failure_message' => null,
            ]);

            $isLate = in_array($booking->status, [BookingStatus::Expired, BookingStatus::Cancelled, BookingStatus::PaymentFailed], true)
                || ($booking->status === BookingStatus::PendingPayment && $booking->hold_expires_at?->isPast());

            if ($isLate) {
                $booking->update([
                    'status' => BookingStatus::RefundPending,
                    'payment_status' => PaymentStatus::RefundPending,
                ]);

                return true;
            }

            if ($slot->reservation_status !== SlotReservationStatus::Held || (int) $slot->reserved_booking_id !== (int) $booking->id) {
                $booking->update([
                    'status' => BookingStatus::RefundPending,
                    'payment_status' => PaymentStatus::RefundPending,
                ]);

                return true;
            }

            $booking->update([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Succeeded,
                'hold_expires_at' => null,
            ]);
            $slot->update([
                'is_booked' => true,
                'reservation_status' => SlotReservationStatus::Booked,
                'reserved_until' => null,
            ]);
            $this->wallets->record(
                $lockedPayment,
                WalletTransactionType::CardCredit,
                $lockedPayment->doctor_amount_cents,
                "card-credit:{$lockedPayment->uuid}",
                ['commission_amount_cents' => $lockedPayment->commission_amount_cents],
            );
            $booking->patient->notify((new PaymentSucceededNotification($booking, $lockedPayment))->afterCommit());
            $booking->doctor->notify((new PaymentSucceededNotification($booking, $lockedPayment))->afterCommit());

            return false;
        }, attempts: 3);

        if ($mustRefund) {
            $this->refunds->refundFull($payment->fresh(), 'late_or_invalid_booking_success');
        }
    }

    /** @param array<string, mixed> $payload */
    private function handleFailure(Payment $payment, string $transactionId, array $payload): void
    {
        DB::transaction(function () use ($payment, $transactionId, $payload): void {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);
            $slot = AvailabilitySlot::query()->lockForUpdate()->findOrFail($booking->availability_slot_id);

            if ($lockedPayment->status === PaymentStatus::Succeeded) {
                return;
            }

            $lockedPayment->update([
                'status' => PaymentStatus::Failed,
                'provider_transaction_id' => $transactionId ?: null,
                'failure_code' => (string) (Arr::get($payload, 'data.message') ?? 'card_declined'),
                'failure_message' => 'تم رفض عملية الدفع من بوابة الدفع.',
                'failed_at' => now(),
            ]);
            $booking->update([
                'status' => BookingStatus::PaymentFailed,
                'payment_status' => PaymentStatus::Failed,
                'hold_expires_at' => null,
            ]);

            if ((int) $slot->reserved_booking_id === (int) $booking->id) {
                $slot->update([
                    'is_booked' => false,
                    'reservation_status' => SlotReservationStatus::Available,
                    'reserved_booking_id' => null,
                    'reserved_until' => null,
                ]);
            }

            $booking->patient->notify((new PaymentFailedNotification($booking, $lockedPayment))->afterCommit());
            $booking->doctor->notify((new PaymentFailedNotification($booking, $lockedPayment))->afterCommit());
        }, attempts: 3);
    }

    /** @param array<string, mixed> $payload */
    private function validateTransaction(Payment $payment, array $payload, string $reference): void
    {
        $configuredIntegrationId = (int) config('services.paymob.card_integration_id');
        $transactionId = (string) Arr::get($payload, 'id', '');
        $transactionUsedByAnotherPayment = $transactionId !== ''
            && Payment::query()
                ->where('provider_transaction_id', $transactionId)
                ->where('id', '!=', $payment->id)
                ->exists();

        if ($transactionId === ''
            || $transactionUsedByAnotherPayment
            || (int) Arr::get($payload, 'amount_cents') !== $payment->amount_cents
            || strtoupper((string) Arr::get($payload, 'currency')) !== strtoupper($payment->currency)
            || ($configuredIntegrationId > 0 && (int) Arr::get($payload, 'integration_id') !== $configuredIntegrationId)
            || ($reference !== '' && $reference !== $payment->uuid)) {
            throw new PaymentDomainException('Paymob transaction data mismatch.', 'webhook_data_mismatch', 422);
        }
    }
}
