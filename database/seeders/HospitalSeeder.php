<?php

namespace Database\Seeders;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where("role", "admin")->first() ?? User::factory()->create(["role" => "admin"]);

        Hospital::factory()
            ->count(5)
            ->state(["admin_id" => $admin->id])
            ->create();
    }
}
