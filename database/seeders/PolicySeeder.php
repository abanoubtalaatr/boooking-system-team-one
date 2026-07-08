<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        Policy::create([
            'type' => 'privacy',
            'content' => 'Your personal information is protected and used only to provide healthcare services.',
            'is_active' => true,
        ]);

        Policy::create([
            'type' => 'terms',
            'content' => 'By using this application, you agree to comply with all applicable terms and conditions.',
            'is_active' => true,
        ]);
    }
}
