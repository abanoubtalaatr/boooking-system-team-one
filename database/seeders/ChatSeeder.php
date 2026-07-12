<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $patient = Patient::factory()->create([
            'name' => 'مريض تجريبي',
            'phone' => '01000000001',
        ]);

        $doctor = User::factory()->create([
            'name' => 'دكتور تجريبي',
            'email' => 'doctor.test@example.com',
        ]);

        $conversation = Conversation::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        // كام رسالة تجريبية متبادلة بين الاتنين
        Message::factory()->fromPatient($patient)->create([
            'conversation_id' => $conversation->id,
            'body' => 'السلام عليكم دكتور، عندي استفسار',
        ]);

        Message::factory()->fromDoctor($doctor)->create([
            'conversation_id' => $conversation->id,
            'body' => 'أهلًا بيك، اتفضل',
        ]);

        // Token جاهز تستخدمه في Postman على طول
        $token = $patient->createToken('postman-test')->plainTextToken;

        $this->command->info('Patient ID: '.$patient->id);
        $this->command->info('Doctor ID: '.$doctor->id);
        $this->command->info('Conversation ID: '.$conversation->id);
        $this->command->info('Patient Token: '.$token);
    }
}