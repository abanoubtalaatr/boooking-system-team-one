<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\UserRole;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'user_id' => User::where('role', UserRole::Doctor)->inRandomOrder()->value('id'),
            //'user_id' => User::factory()->doctor(),  // instead of 'user_id' => User::factory(),
            'patient_id' => Patient::factory(),
            'comment' => $this->faker->sentence,
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}
