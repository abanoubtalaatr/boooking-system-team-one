<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SlotReservationStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payments\RefundService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelBookingAction
{
    public function __construct(private readonly RefundService $refunds) {}

    public function __invoke(Booking $booking): Booking
    {
        $paymentToRefund = DB::transaction(function () use ($booking): ?Payment {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (in_array($lockedBooking->status, [BookingStatus::Completed, BookingStatus::Rejected, BookingStatus::Expired, BookingStatus::Refunded], true)) {
                throw ValidationException::withMessages(['booking' => 'This booking cannot be cancelled.']);
            }

            if ($lockedBooking->status === BookingStatus::Cancelled) {
                throw ValidationException::withMessages(['booking' => 'Booking is already cancelled.']);
            }

            $payment = $lockedBooking->payments()->latest('id')->lockForUpdate()->first();

            if ($lockedBooking->status === BookingStatus::RefundPending && $payment) {
                return $payment;
            }

            if ($payment?->method === PaymentMethod::Card && $payment->status === PaymentStatus::Succeeded) {
                $lockedBooking->update([
                    'status' => BookingStatus::RefundPending,
                    'payment_status' => PaymentStatus::RefundPending,
                ]);

                return $payment;
            }

            $paymentStatus = $lockedBooking->payment_status;

            if ($payment?->method === PaymentMethod::Cash && $payment->status === PaymentStatus::CashDue) {
                $payment->update(['status' => PaymentStatus::Voided]);
                $paymentStatus = PaymentStatus::Voided;
            }

            $lockedBooking->update([
                'status' => BookingStatus::Cancelled,
                'payment_status' => $paymentStatus,
                'hold_expires_at' => null,
            ]);
            $lockedBooking->slot()->where('reserved_booking_id', $lockedBooking->id)->update([
                'is_booked' => false,
                'reservation_status' => SlotReservationStatus::Available,
                'reserved_booking_id' => null,
                'reserved_until' => null,
            ]);

            return null;
        }, attempts: 3);

        if ($paymentToRefund) {
            $this->refunds->refundFull($paymentToRefund, 'patient_cancellation');
        }

        return $booking->fresh(['doctor.doctorProfile.specialty', 'slot', 'payments']);
    }
}
