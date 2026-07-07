<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),

            'description' => fake()->paragraph(),

            'image' => null,

            'start_date' => now(),

            'end_date' => now()->addDays(30),

            'is_active' => true,
        ];
    }
}
