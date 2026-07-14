<?php

namespace App\Actions\Chat;

use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\DoctorMessageNotification;
use App\Notifications\PatientMessageNotification;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SendMessageAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
        private readonly ConversationRepositoryInterface $conversations,
    ) {
    }

    public function handle(Conversation $conversation, string $senderId, array $data, ?UploadedFile $file = null): Message
    {
        $message = DB::transaction(function () use ($conversation, $senderId, $data, $file) {
            $message = $this->messages->create([
                "conversation_id" => $conversation->id,
                "sender_id" => $senderId,
                "type" => $data["type"],
                "content" => $data["content"] ?? null,
            ]);

            $type = MessageType::from($data["type"]);

            if ($type !== MessageType::Text && $file) {
                $message->addMedia($file)->toMediaCollection($type->value);
            }

            $this->conversations->touchLastMessageAt($conversation);

            return $message;
        });

        broadcast(new MessageSent($message))->toOthers();

        $recipient = $conversation->otherParticipant($senderId);

        $recipient->notify(
            $recipient->id === $conversation->doctor_id
                ? new DoctorMessageNotification($message)
                : new PatientMessageNotification($message)
        );

        return $message;
    }
}
