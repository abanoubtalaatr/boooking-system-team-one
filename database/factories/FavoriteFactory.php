<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'doctor_id' => User::whereHas('doctorProfile')
            ->inRandomOrder()
            ->value('id'),

        'user_id' => Patient::query()
            ->inRandomOrder()
            ->value('id'),
        ];
    }
}

