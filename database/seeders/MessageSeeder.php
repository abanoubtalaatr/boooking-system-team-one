<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        Conversation::all()->each(function (Conversation $conversation) {

            Message::factory()
                ->count(fake()->numberBetween(3, 10))
                ->state(function () use ($conversation) {

                    $doctorSender = fake()->boolean();

                    return [

                        'conversation_id' => $conversation->id,

                        'sender_id' => $doctorSender
                            ? $conversation->doctor_id
                            : $conversation->patient_id,

                        'sender_type' => $doctorSender
                            ? User::class
                            : Patient::class,

                    ];
                })
                ->create();

        });
    }
}