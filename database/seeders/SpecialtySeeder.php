<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role(['admin', 'super-admin'])->first() ?? User::factory()->admin()->create();

        Specialty::factory()
            ->count(8)
            ->state(['admin_id' => $admin->id])
            ->create();
    }
}
