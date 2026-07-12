<?php

namespace App\Actions\Booking;

use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\DoctorProfile;
use App\Models\User;

class CreateBookingAction
{
    /**
     * Create a new class instance.
     */

    public function __invoke(array $data): Booking
    {
        return DB::transaction(function () use ($data) {

            $slot = AvailabilitySlot::query()
                ->whereKey($data['availability_slot_id'])
                ->firstOrFail();

            if ($slot->doctor_id != $data['doctor_id']) {
                throw ValidationException::withMessages([
                    'availability_slot_id' => 'Invalid availability slot.',
                ]);
            }

            if ($slot->is_booked) {
                throw ValidationException::withMessages([
                    'availability_slot_id' => 'This slot is already booked.',
                ]);
            }

            $doctor = User::query()
                ->with('doctorProfile')
                ->findOrFail($data['doctor_id']);

            $doctorProfile = $doctor->doctorProfile;

            $patientId = 1; // For testing purposes

            $booking = Booking::create([

                'booking_number' => $this->generateBookingNumber(),

                'patient_id' => $patientId,

                'doctor_id' => $data['doctor_id'],

                'availability_slot_id' => $slot->id,

                'booking_date' => $slot->day,

                'booking_time' => $slot->start_time,

                'consultation_type' => $data['consultation_type'],

                'price' => $doctorProfile->consultation_fee,

                'status' => BookingStatus::Pending,

                'payment_status' => PaymentStatus::Pending,

            ]);

            $slot->update([
                'is_booked' => true,
            ]);

            return $booking->fresh([
                'doctor.doctorProfile.specialization',
                'slot',
            ]);
        });
    }

    private function generateBookingNumber(): string
    {
        return 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
