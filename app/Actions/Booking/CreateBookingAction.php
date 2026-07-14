<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    public function __invoke(array $data, int $patientId): Booking
    {
        return DB::transaction(function () use ($data, $patientId) {
            $slot = AvailabilitySlot::query()
                ->whereKey($data['availability_slot_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $slot->doctor_id !== (int) $data['doctor_id']) {
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

            if (! $doctorProfile) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Doctor profile not found.',
                ]);
            }

            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'patient_id' => $patientId,
                'doctor_id' => $data['doctor_id'],
                'availability_slot_id' => $slot->id,
                'booking_date' => $slot->day,
                'booking_time' => $slot->start_time,
                'consultation_type' => $data['consultation_type'],
                'price' => $doctorProfile->price,
                'status' => BookingStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
            ]);

            $slot->update([
                'is_booked' => true,
            ]);

            return $booking->fresh([
                'doctor.doctorProfile.specialty',
                'slot',
            ]);
        });
    }

    private function generateBookingNumber(): string
    {
        return 'BK-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
