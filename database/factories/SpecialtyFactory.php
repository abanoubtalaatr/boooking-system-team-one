<?php

namespace Database\Factories;

use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    public function definition(): array
    {
        return [
            'admin_id' => User::factory()->admin(),
            'name' => fake()->unique()->randomElement([
                'Cardiology', 'Dermatology', 'Pediatrics', 'Neurology',
                'Orthopedics', 'Psychiatry', 'Radiology', 'Oncology',
            ]),
        ];
    }
}
