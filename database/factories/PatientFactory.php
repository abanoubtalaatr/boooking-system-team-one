<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('010########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Password123'),
            'verified_at' => now(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'verified_at' => null,
        ]);
    }
}
