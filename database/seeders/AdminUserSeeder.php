<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            "name" => "Cure Admin",
            "email" => "admin@cure.test",
            "password" => Hash::make("password"),
            "role" => "admin",
            "status" => "active",
        ]);
    }
}
