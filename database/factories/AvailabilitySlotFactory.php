<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilitySlotFactory extends Factory
{
    public function definition(): array
    {
        $day = fake()->dateTimeBetween('today', '+30 days');

        $hour = fake()->numberBetween(9, 16);

        return [
            'doctor_id' => User::query()->inRandomOrder()->value('id'),

            'day' => $day->format('Y-m-d'),

            'start_time' => sprintf('%02d:00:00', $hour),

            'end_time' => sprintf('%02d:30:00', $hour),

            'is_booked' => false,
        ];
    }
}
