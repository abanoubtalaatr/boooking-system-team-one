<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            RolesAndPermissionsSeeder::class,
            SettingSeeder::class,
            SpecializationSeeder::class,
            FaqCategorySeeder::class,
            FaqSeeder::class,
            PolicySeeder::class,
            HospitalSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            ReviewSeeder::class,
            AvailabilitySlotSeeder::class,
            FavoriteSeeder::class,
            BookingSeeder::class,
            ConversationSeeder::class,
            MessageSeeder::class,
            PlatformDemoSeeder::class,
            NoShowReportsDemoSeeder::class,
        ]);
    }
}
