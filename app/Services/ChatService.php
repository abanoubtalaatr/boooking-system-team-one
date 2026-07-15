<?php

namespace App\Services;

use App\Actions\Chat\DeleteMessageAction;
use App\Actions\Chat\MarkMessagesAsSeenAction;
use App\Actions\Chat\SendMessageAction;
use App\Actions\Chat\StartConversationAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * نقطة الدخول الوحيدة اللي الـ Controllers بتستخدمها لـ start/send/list/seen/delete.
 */
class ChatService
{
    public function __construct(
        private MessageRepositoryInterface $messages,
        private SendMessageAction $sendMessage,
        private StartConversationAction $startConversation,
        private MarkMessagesAsSeenAction $markSeen,
        private DeleteMessageAction $deleteMessage,
    ) {}

    public function startOrGet(Patient $patient, User $doctor): Conversation
    {
        return $this->startConversation->handle($patient, $doctor);
    }

    public function send(Conversation $conversation, Model $sender, array $data): Message
    {
        return $this->sendMessage->handle($conversation, $sender, $data);
    }

    public function listMessages(Conversation $conversation): LengthAwarePaginator
    {
        return $this->messages->paginateForConversation($conversation->id);
    }

    public function markRead(Conversation $conversation, Model $reader): int
    {
        return $this->markSeen->handle($conversation, $reader);
    }

    public function delete(Message $message): bool
    {
        return $this->deleteMessage->handle($message);
    }
}