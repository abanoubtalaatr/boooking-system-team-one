<?php

namespace Database\Factories;

use App\Models\AvailabilitySlot;
use App\Models\DoctorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilitySlotFactory extends Factory
{
    protected $model = AvailabilitySlot::class;

    public function definition(): array
    {
        $start = fake()->numberBetween(8, 16);

        return [
            "doctor_id" => DoctorProfile::factory(),
            "day" => fake()->dateTimeBetween("now", "+2 weeks")->format("Y-m-d"),
            "start_time" => sprintf("%02d:00:00", $start),
            "end_time" => sprintf("%02d:00:00", $start + 1),
            "is_booked" => false,
        ];
    }
}
