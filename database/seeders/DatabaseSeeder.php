<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\Promotion;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Specialization::factory()->count(20)->create();

        $users = User::factory(10)->create();

        foreach ($users as $user) {
            DoctorProfile::factory()->create([
                'user_id' => $user->id,
                'specialization_id' => Specialization::inRandomOrder()->first()->id,
            ]);
        }

        Patient::factory()->count(20)->create();

        Promotion::factory()->count(5)->create();

        $this->call([
            SpecializationSeeder::class,
            FaqCategorySeeder::class,
            FaqSeeder::class,
            PolicySeeder::class,
        ]);
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
