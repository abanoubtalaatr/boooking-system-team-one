<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['name' => 'Cardiology', 'image' => 'specializations/cardiology.png'],
            ['name' => 'Dentistry', 'image' => 'specializations/dentistry.png'],
            ['name' => 'Dermatology', 'image' => 'specializations/Dermatology.png'],
            ['name' => 'Neurology', 'image' => 'specializations/neurology.png'],
            ['name' => 'Orthopedics', 'image' => 'specializations/orthopedics.png'],
            ['name' => 'Pediatrics', 'image' => 'specializations/pediatrics.png'],
            ['name' => 'Psychiatry', 'image' => 'specializations/psychiatry.png'],
            ['name' => 'Ophthalmology', 'image' => 'specializations/ophthalmology.png'],
            ['name' => 'ENT', 'image' => 'specializations/ent.png'],
            ['name' => 'Urology', 'image' => 'specializations/urology.png'],
        ];

        foreach ($specializations as $specialization) {
            Specialization::create($specialization);
        }
    }
}
