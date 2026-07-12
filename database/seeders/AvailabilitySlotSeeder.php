<?php

namespace Database\Seeders;

use App\Models\AvailabilitySlot;
use Illuminate\Database\Seeder;

class AvailabilitySlotSeeder extends Seeder
{
    public function run(): void
    {
        AvailabilitySlot::factory(50)->create();
    }
}
