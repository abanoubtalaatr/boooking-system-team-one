<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DoctorProfile;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorProfile>
 */
class DoctorProfileFactory extends Factory
{
    protected $model = DoctorProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialization_id' => Specialization::factory(),

            'bio' => fake()->paragraph(),

            'experience_years' => rand(1, 20),

            'consultation_fee' => rand(100, 1000),

            'rating' => rand(30, 50) / 10,

            'address' => fake()->address(),

            'latitude' => fake()->randomFloat(7, 29.90, 30.20),

            'longitude' => fake()->randomFloat(7, 31.10, 31.40),

            'image' => null,

            'is_available' => true,
        ];
    }
}
