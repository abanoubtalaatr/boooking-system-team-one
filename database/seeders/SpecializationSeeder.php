<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['name' => 'Cardiology', 'description' => 'Heart and blood vessel specialist'],
            ['name' => 'Dentistry', 'description' => 'Dental care and oral health'],
            ['name' => 'Dermatology', 'description' => 'Skin specialist'],
            ['name' => 'Neurology', 'description' => 'Brain and nervous system specialist'],
            ['name' => 'Orthopedics', 'description' => 'Bones and joints specialist'],
            ['name' => 'Pediatrics', 'description' => 'Child healthcare specialist'],
            ['name' => 'Psychiatry', 'description' => 'Mental health specialist'],
            ['name' => 'Ophthalmology', 'description' => 'Eye specialist'],
            ['name' => 'ENT', 'description' => 'Ear, Nose and Throat specialist'],
            ['name' => 'Urology', 'description' => 'Urinary tract specialist'],
        ];

        foreach ($specializations as $specialization) {
            Specialization::create($specialization);
        }
    }
}
