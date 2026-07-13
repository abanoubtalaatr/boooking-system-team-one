<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelBookingAction
{
    public function __invoke(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {

            if ($booking->status === BookingStatus::Completed) {
                throw ValidationException::withMessages([
                    'booking' => 'Completed bookings cannot be cancelled.',
                ]);
            }

            if ($booking->status === BookingStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking is already cancelled.',
                ]);
            }

            if ($booking->status === BookingStatus::Rejected) {
                throw ValidationException::withMessages([
                    'booking' => 'Rejected bookings cannot be cancelled.',
                ]);
            }

            if ($booking->status === BookingStatus::Expired) {
                throw ValidationException::withMessages([
                    'booking' => 'Expired bookings cannot be cancelled.',
                ]);
            }

            $booking->update([
                'status' => BookingStatus::Cancelled,
            ]);

            $booking->slot()->update([
                'is_booked' => false,
            ]);

            return $booking->fresh([
                'doctor.doctorProfile.specialty',
                'slot',
            ]);
        });
    }
}
