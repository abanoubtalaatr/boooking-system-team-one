<?php

namespace App\Actions\Chat;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
//use App\Notifications\ChatMessageReceived;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class SendMessageAction
{
    public function __construct(
        private MessageRepositoryInterface $messages,
    ) {}

    public function handle(Conversation $conversation, Model $sender, array $data): Message
    {
        $message = $this->messages->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'type' => $data['type'],
            'body' => $data['body'] ?? null,
        ]);

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $message->addMedia($data['attachment'])->toMediaCollection('attachment');
        }

        $conversation->update(['last_message_at' => now()]);

        $message->load('media');

        broadcast(new MessageSent($message))->toOthers();

        $recipient = $sender instanceof \App\Models\Patient
            ? $conversation->doctor
            : $conversation->patient;

        //$recipient?->notify(new ChatMessageReceived($message));

        return $message;
    }
}