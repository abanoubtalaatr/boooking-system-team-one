<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::query()->get();
        $doctors = User::role('doctor')->get();

        if ($patients->isEmpty()) {
            $patients = Patient::factory()->count(5)->create();
        }

        if ($doctors->isEmpty()) {
            $doctors = User::factory()->doctor()->count(5)->create();
        }

        foreach ($patients as $patient) {
            Conversation::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctors->random()->id,
            ]);
        }
    }
}
