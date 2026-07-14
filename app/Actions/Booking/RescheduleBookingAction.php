<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RescheduleBookingAction
{
    public function __invoke(Booking $booking, int $patientId, array $data): Booking {

        return DB::transaction(function () use (
            $booking,
            $patientId,
            $data
        ) {

            if ($booking->patient_id !== $patientId) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking not found.',
                ]);
            }

            if (in_array($booking->status, [
                BookingStatus::Completed,
                BookingStatus::Cancelled,
                BookingStatus::Rejected,
                BookingStatus::Expired,
            ], true)) {

                throw ValidationException::withMessages([
                    'booking' => 'This booking cannot be rescheduled.',
                ]);
            }

            $newSlot = AvailabilitySlot::findOrFail(
                $data['availability_slot_id']
            );

            if ($newSlot->doctor_id !== $booking->doctor_id) {

                throw ValidationException::withMessages([
                    'availability_slot_id' =>
                        'This slot does not belong to this doctor.',
                ]);
            }

            if ($newSlot->is_booked) {

                throw ValidationException::withMessages([
                    'availability_slot_id' =>
                        'This slot is already booked.',
                ]);
            }

            $booking->slot->update([
                'is_booked' => false,
            ]);

            $newSlot->update([
                'is_booked' => true,
            ]);

            $booking->update([
                'availability_slot_id' => $newSlot->id,
                'booking_date' => $newSlot->day,
                'booking_time' => $newSlot->start_time,
                'status' => BookingStatus::Rescheduled,
            ]);

            return $booking->fresh([
                'doctor.doctorProfile.specialty',
                'slot',
            ]);
        });
    }
}
