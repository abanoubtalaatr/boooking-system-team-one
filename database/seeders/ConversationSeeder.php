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
        $patients = Patient::all();

        $doctors = User::where('role', 'doctor')->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('Patients or doctors not found.');
            return;
        }

        foreach ($patients as $patient) {

            Conversation::factory()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctors->random()->id,
            ]);

        }
    }
}