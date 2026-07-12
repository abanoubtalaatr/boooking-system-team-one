<?php

namespace Database\Factories;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorProfileFactory extends Factory
{
    protected $model = DoctorProfile::class;

    public function definition(): array
    {
        return [
            "user_id" => User::factory()->state(["role" => "doctor", "status" => "active"]),
            "bio" => fake()->paragraph(),
            "consultation_price" => fake()->randomFloat(2, 20, 300),
            "is_approved" => fake()->boolean(80),
        ];
    }

    public function pendingProfile(): static
    {
        return $this->state(fn () => [
            "bio" => null,
            "consultation_price" => null,
            "is_approved" => false,
        ]);
    }
}
