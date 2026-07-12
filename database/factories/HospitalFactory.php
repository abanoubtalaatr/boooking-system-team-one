<?php

namespace Database\Factories;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HospitalFactory extends Factory
{
    protected $model = Hospital::class;

    public function definition(): array
    {
        return [
            "admin_id" => User::factory()->state(["role" => "admin"]),
            "name" => fake()->company() . " Hospital",
            "latitude" => fake()->latitude(29, 31),
            "longitude" => fake()->longitude(30, 32),
        ];
    }
}
