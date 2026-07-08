<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\DoctorProfile;
use Illuminate\Database\Seeder;

class AvailabilitySlotSeeder extends Seeder
{
    public function run(): void
    {
        DoctorProfile::all()->each(function (DoctorProfile $doctor) {
            AvailabilitySlot::factory()
                ->count(5)
                ->state(["doctor_id" => $doctor->id])
                ->create();
        });
    }
}
