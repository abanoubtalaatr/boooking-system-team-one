<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'camila.herman@example.net'],
            [
                'name' => 'Camila Herman',
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@cure.test'],
            [
                'name' => 'مدير المنصة التجريبي',
                'status' => 'active',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_by' => $superAdmin->id,
            ],
        );
    }
}
