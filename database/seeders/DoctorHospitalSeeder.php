<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\Hospital;
use Illuminate\Database\Seeder;

class DoctorHospitalSeeder extends Seeder
{
    public function run(): void
    {
        $hospitalIds = Hospital::pluck("id");

        DoctorProfile::all()->each(function (DoctorProfile $doctor) use ($hospitalIds) {
            $doctor->hospitals()->sync($hospitalIds->random(min(2, $hospitalIds->count()))->all());
        });
    }
}
