<?php
namespace Database\Seeders;

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
        // User::factory(10)->create();

        /* User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
        $this->call([
            SpecializationSeeder::class,
            HospitalSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            ReviewSeeder::class,
            AvailabilitySlotSeeder::class,
            FavoriteSeeder::class,
           // ConversationSeeder::class,
           // MessageSeeder::class,
        ]);
    }
}
