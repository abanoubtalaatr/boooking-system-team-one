<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AvailabilitySlotFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->numberBetween(9, 16); // من 9 صباحًا لـ 4 عصرًا

        return [
            'doctor_id'  => User::factory(),
            'day'        => Carbon::today()->addDays($this->faker->numberBetween(0, 14)),
            'start_time' => sprintf('%02d:00:00', $start),
            'end_time'   => sprintf('%02d:00:00', $start + 1),
            'is_booked'  => false,
        ];
    }

    public function booked(): static
    {
        return $this->state(fn (array $attributes) => ['is_booked' => true]);
    }
}