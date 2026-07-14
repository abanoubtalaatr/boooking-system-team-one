<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class DoctorSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialtyIds = Specialty::pluck("id");

        DoctorProfile::all()->each(function (DoctorProfile $doctor) use ($specialtyIds) {
            $doctor->specialties()->sync($specialtyIds->random(min(2, $specialtyIds->count()))->all());
        });
    }
}
