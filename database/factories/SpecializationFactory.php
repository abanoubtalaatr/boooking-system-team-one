<?php

namespace Database\Factories;

use App\Models\Specialization;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialization::class;

    public function definition(): array
    {
        return [
            "name" => fake()->unique()->randomElement([
                "Cardiology", "Dermatology", "Pediatrics", "Neurology",
                "Orthopedics", "Psychiatry", "Radiology", "Oncology",
            ]),
        ];
    }
}
