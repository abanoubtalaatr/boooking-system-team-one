<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $patients = User::where("role", "patient")->get();
        $doctors = User::where("role", "doctor")->get();

        if ($patients->isEmpty()) {
            $patients = User::factory()->count(5)->state(["role" => "patient"])->create();
        }

        foreach ($patients as $patient) {
            Conversation::factory()->create([
                "patient_id" => $patient->id,
                "doctor_id" => $doctors->random()->id,
            ]);
        }
    }
}
