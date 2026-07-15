<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {

        $patients = Patient::query()->get();
        $doctorIds = User::query()
            ->whereHas('doctorProfile')
            ->pluck('id');

        if ($patients->isEmpty() || $doctorIds->isEmpty()) {
            return;
        }

        foreach ($patients as $patient) {
            $favoriteDoctorIds = $doctorIds->shuffle()->take(min(random_int(1, 15), $doctorIds->count()));

            foreach ($favoriteDoctorIds as $doctorId) {
                Favorite::query()->firstOrCreate([
                    'user_id' => $patient->id,
                    'doctor_id' => $doctorId,
                ]);
            }
        }
    }
}
