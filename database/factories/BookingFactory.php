<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\ConsultationType;
use App\Enums\PaymentStatus;
use App\Models\AvailabilitySlot;
use App\Models\DoctorProfile;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $slot = AvailabilitySlot::query()
            ->where('is_booked', false)
            ->inRandomOrder()
            ->first();

        $doctor = DoctorProfile::where('user_id', $slot->doctor_id)->first();

        return [

            'booking_number' => 'BK-' . strtoupper(Str::random(8)),

            'patient_id' => Patient::query()->inRandomOrder()->value('id'),

            'doctor_id' => $slot->doctor_id,

            'availability_slot_id' => $slot->id,

            'booking_date' => $slot->day,

            'booking_time' => $slot->start_time,

            'consultation_type' => fake()->randomElement(
                array_column(ConsultationType::cases(), 'value')
            ),

            'price' => $doctor->consultation_fee,

            'status' => fake()->randomElement(
                array_column(BookingStatus::cases(), 'value')
            ),

            'payment_status' => fake()->randomElement(
                array_column(PaymentStatus::cases(), 'value')
            ),
        ];
    }
}
