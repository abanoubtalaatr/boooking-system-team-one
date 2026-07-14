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
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

/**
 * Single entry point controllers use for start/send/list/seen/delete.
 */
// app/Services/Chat/ChatService.php
class ChatService
{
    public function __construct(
        private ConversationRepositoryInterface $conversations,
        private MessageRepositoryInterface $messages,
        private SendMessageAction $sendMessage,
        private StartConversationAction $startConversation,
    ) {}

    public function startOrGet(Patient $patient, User $doctor): Conversation
    {
        return $this->startConversation->execute($patient, $doctor);
    }

    public function send(Conversation $conversation, Model $sender, array $data): Message
    {
        return $this->sendMessage->execute($conversation, $sender, $data);
    }

    public function listMessages(Conversation $conversation): LengthAwarePaginator
    {
        return $this->messages->paginateForConversation($conversation->id);
    }

    public function markRead(Conversation $conversation, Model $reader): void
    {
        $this->messages->markAsRead($conversation, $reader);
    }
}