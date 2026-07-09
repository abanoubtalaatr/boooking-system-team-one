<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Appointments',
            'Doctors',
            'Payments',
            'Account',
            'General',
        ];

        foreach ($categories as $category) {
            FaqCategory::create([
                'name' => $category,
            ]);
        }
    }
}   
