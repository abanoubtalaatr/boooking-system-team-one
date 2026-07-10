<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where("role", "admin")->first() ?? User::factory()->create(["role" => "admin"]);

        DoctorProfile::factory()
            ->count(10)
            ->state(fn () => [
                "user_id" => User::factory()->create([
                    "password" => Hash::make("password"),
                    "role" => "doctor",
                  
                ])->id,
            ])
            ->create();
    }
}
