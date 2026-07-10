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
        return [
            'conversation_id' => Conversation::factory(),
            'type' => 'text',
            'body' => $this->faker->sentence(),
        ];
    }

    // استخدام: Message::factory()->fromPatient($patient)->create([...])
    public function fromPatient($patient): static
    {
        return $this->state(fn () => [
            'sender_id' => $patient->id,
            'sender_type' => get_class($patient),
        ]);
    }

    // استخدام: Message::factory()->fromDoctor($doctor)->create([...])
    public function fromDoctor($doctor): static
    {
        return $this->state(fn () => [
            'sender_id' => $doctor->id,
            'sender_type' => get_class($doctor),
        ]);
    }
}