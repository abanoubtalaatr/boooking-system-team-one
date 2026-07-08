<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $conversation = Conversation::factory()->create();

        return [
            "conversation_id" => $conversation->id,
            "sender_id" => fake()->boolean() ? $conversation->patient_id : $conversation->doctor_id,
            "type" => "text",
            "content" => fake()->sentence(),
            "status" => fake()->randomElement(["sent", "delivered", "seen"]),
        ];
    }
}
