<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AvailabilitySlotSeeder extends Seeder
{
    public function run(): void
    {
        $doctorIds = User::whereHas('doctorProfile')->pluck('id');
       
        $hours = range(9, 16); // 9 صباحًا لـ 4 عصرًا

        foreach ($doctorIds as $doctorId) {
            for ($d = 0; $d < 7; $d++) { // أسبوع قدام
                $day = Carbon::today()->addDays($d);

                foreach ($hours as $hour) {
                    AvailabilitySlot::firstOrCreate([
                        'doctor_id'  => $doctorId,
                        'day'        => $day->toDateString(),
                        'start_time' => sprintf('%02d:00:00', $hour),
                        'end_time'   => sprintf('%02d:00:00', $hour + 1),
                    ], [
                        'is_booked' => fake()->boolean(20), // 20% محجوزة
                    ]);
                }
            }
        }
    }
}