<?php

namespace Database\Factories;

use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $slot = AvailabilitySlot::factory()->create();

        return [
            "patient_id" => User::factory()->state(["role" => "patient"]),
            "doctor_id" => $slot->doctor_id,
            "slot_id" => $slot->id,
            "status" => "pending",
            "price" => fake()->randomFloat(2, 20, 300),
            "payment_status" => "pending",
        ];
    }
}
