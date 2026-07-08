<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    private const PATIENT_EMAIL = 'test@example.com';

    private const FAVORITE_COUNT = 3;

    /**
     * @var list<array{name: string, email: string}>
     */
    private const DEMO_DOCTORS = [
        ['name' => 'Dr. John Smith', 'email' => 'dr.smith@example.com'],
        ['name' => 'Dr. Emily Johnson', 'email' => 'dr.johnson@example.com'],
        ['name' => 'Dr. Michael Williams', 'email' => 'dr.williams@example.com'],
        ['name' => 'Dr. Sarah Brown', 'email' => 'dr.brown@example.com'],
    ];

    /**
     * Seed favorite doctors for demo users.
     */
    public function run(): void
    {
        $patient = User::query()
            ->where('email', self::PATIENT_EMAIL)
            ->first();

        if (! $patient) {
            return;
        }

        $doctors = collect(self::DEMO_DOCTORS)
            ->map(fn (array $doctor) => User::query()->firstOrCreate(
                ['email' => $doctor['email']],
                $doctor,
            ))
            ->take(self::FAVORITE_COUNT);

        foreach ($doctors as $doctor) {
            Favorite::query()->firstOrCreate([
                'user_id' => $patient->id,
                'doctor_id' => $doctor->id,
            ]);
        }
    }
}
