<?php

namespace App\Services;

use App\Actions\Chat\DeleteMessageAction;
use App\Actions\Chat\MarkMessagesAsSeenAction;
use App\Actions\Chat\SendMessageAction;
use App\Actions\Chat\StartConversationAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

/**
 * Single entry point controllers use for start/send/list/seen/delete.
 */
class ChatService
{
    public function __construct(
        private readonly StartConversationAction $startConversation,
        private readonly SendMessageAction $sendMessage,
        private readonly MarkMessagesAsSeenAction $markMessagesAsSeen,
        private readonly DeleteMessageAction $deleteMessage,
        private readonly ConversationRepositoryInterface $conversations,
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    public function start(string $patientId, string $doctorId): Conversation
    {
        return $this->startConversation->handle($patientId, $doctorId);
    }

    public function listForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->conversations->paginateForUser($userId, $perPage);
    }

    public function messagesFor(string $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->messages->paginateForConversation($conversationId, $perPage);
    }

    public function send(Conversation $conversation, string $senderId, array $data, ?UploadedFile $file = null): Message
    {
        return $this->sendMessage->handle($conversation, $senderId, $data, $file);
    }

    public function markSeen(Conversation $conversation, string $userId): int
    {
        return $this->markMessagesAsSeen->handle($conversation, $userId);
    }

    public function deleteMessage(Message $message, string $requesterId): bool
    {
        return $this->deleteMessage->handle($message, $requesterId);
    }
}
