<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        Conversation::all()->each(function (Conversation $conversation) {
            Message::factory()
                ->count(fake()->numberBetween(3, 10))
                ->state(fn () => [
                    "conversation_id" => $conversation->id,
                    "sender_id" => fake()->boolean() ? $conversation->patient_id : $conversation->doctor_id,
                ])
                ->create();
        });
    }
}
