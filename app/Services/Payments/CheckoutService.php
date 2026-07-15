<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Data\Payments\CreatePaymentIntentData;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Exceptions\PaymentDomainException;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Payment;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CheckoutService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly MoneyCalculator $money,
        private readonly PlatformCommissionService $commission,
    ) {}

    public function checkout(Booking $booking, Patient $patient, PaymentMethod $method, string $idempotencyKey): Payment
    {
        if ((int) $booking->patient_id !== (int) $patient->id) {
            throw new PaymentDomainException('الحجز غير موجود.', 'booking_not_found', 404);
        }

        $payment = DB::transaction(function () use ($booking, $patient, $method, $idempotencyKey): Payment {
            $existing = Payment::query()
                ->where('patient_id', $patient->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                if ((int) $existing->booking_id !== (int) $booking->id || $existing->method !== $method) {
                    throw new PaymentDomainException('مفتاح Idempotency مستخدم لطلب آخر.', 'idempotency_conflict', 409);
                }

                return $existing;
            }

            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $concurrentPayment = Payment::query()
                ->where('patient_id', $patient->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($concurrentPayment) {
                if ((int) $concurrentPayment->booking_id !== (int) $lockedBooking->id || $concurrentPayment->method !== $method) {
                    throw new PaymentDomainException('مفتاح Idempotency مستخدم لطلب آخر.', 'idempotency_conflict', 409);
                }

                return $concurrentPayment;
            }

            $slot = AvailabilitySlot::query()->lockForUpdate()->findOrFail($lockedBooking->availability_slot_id);
            $this->assertCheckoutAllowed($lockedBooking, $slot);

            $amountCents = $this->money->decimalToCents((string) $lockedBooking->price);
            $commissionBps = $this->commission->bookingCommissionBasisPoints($method);
            $commissionAmountCents = $this->money->basisPointsAmount($amountCents, $commissionBps);

            $createdPayment = Payment::query()->create([
                'uuid' => (string) Str::uuid(),
                'booking_id' => $lockedBooking->id,
                'patient_id' => $patient->id,
                'doctor_id' => $lockedBooking->doctor_id,
                'method' => $method,
                'status' => $method === PaymentMethod::Cash ? PaymentStatus::CashDue : PaymentStatus::Initiated,
                'amount_cents' => $amountCents,
                'currency' => (string) config('services.paymob.currency', 'EGP'),
                'commission_bps' => $commissionBps,
                'commission_amount_cents' => $commissionAmountCents,
                'doctor_amount_cents' => $amountCents - $commissionAmountCents,
                'idempotency_key' => $idempotencyKey,
                'provider' => $method === PaymentMethod::Card ? 'paymob' : null,
                'expires_at' => $lockedBooking->hold_expires_at,
            ]);

            if ($method === PaymentMethod::Cash) {
                $lockedBooking->update([
                    'status' => BookingStatus::Confirmed,
                    'payment_status' => PaymentStatus::CashDue,
                    'hold_expires_at' => null,
                ]);
                $slot->update([
                    'reservation_status' => SlotReservationStatus::Booked,
                    'reserved_until' => null,
                    'is_booked' => true,
                ]);
            }

            return $createdPayment;
        }, attempts: 3);

        if ($method === PaymentMethod::Cash || $payment->provider_intention_id) {
            return $payment->load('booking');
        }

        try {
            $intent = $this->gateway->createIntent(new CreatePaymentIntentData(
                reference: $payment->uuid,
                amountCents: $payment->amount_cents,
                currency: $payment->currency,
                patientName: $patient->name,
                patientEmail: $patient->email,
                patientPhone: $patient->phone,
            ));

            $payment->update([
                'status' => PaymentStatus::Pending,
                'provider_intention_id' => $intent->intentionId,
                'provider_order_id' => $intent->orderId ?: null,
                'provider_client_secret' => $intent->clientSecret,
                'checkout_url' => $intent->checkoutUrl,
            ]);
        } catch (PaymentDomainException $exception) {
            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_code' => $exception->errorCode,
                'failure_message' => $exception->getMessage(),
                'failed_at' => now(),
            ]);
            $this->releaseFailedBooking($payment->booking);
            $payment->booking->patient->notify((new PaymentFailedNotification($payment->booking, $payment))->afterCommit());
            $payment->booking->doctor->notify((new PaymentFailedNotification($payment->booking, $payment))->afterCommit());

            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            $payment->update([
                'status' => PaymentStatus::PendingVerification,
                'failure_code' => 'provider_outcome_unknown',
                'failure_message' => 'Paymob response outcome is unknown.',
            ]);
        }

        return $payment->fresh('booking');
    }

    private function assertCheckoutAllowed(Booking $booking, AvailabilitySlot $slot): void
    {
        if ($booking->status !== BookingStatus::PendingPayment) {
            throw new PaymentDomainException('الحجز غير متاح للدفع.', 'booking_not_payable', 409);
        }

        if (! $booking->hold_expires_at || $booking->hold_expires_at->isPast()) {
            throw new PaymentDomainException('انتهت مهلة الحجز. اختر موعداً جديداً.', 'booking_hold_expired', 409);
        }

        if ($slot->reservation_status !== SlotReservationStatus::Held || (int) $slot->reserved_booking_id !== (int) $booking->id) {
            throw new PaymentDomainException('الموعد لم يعد محجوزاً لهذا الطلب.', 'slot_hold_lost', 409);
        }
    }

    private function releaseFailedBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking): void {
            $lockedBooking = Booking::query()->lockForUpdate()->find($booking->id);

            if (! $lockedBooking || $lockedBooking->status !== BookingStatus::PendingPayment) {
                return;
            }

            $lockedBooking->update([
                'status' => BookingStatus::PaymentFailed,
                'payment_status' => PaymentStatus::Failed,
                'hold_expires_at' => null,
            ]);
            $lockedBooking->slot()->update([
                'is_booked' => false,
                'reservation_status' => SlotReservationStatus::Available,
                'reserved_booking_id' => null,
                'reserved_until' => null,
            ]);
        });
    }
}
