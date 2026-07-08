<?php

namespace App\Actions\Chat;

use App\Events\MessagesSeen;
use App\Models\Conversation;
use App\Repositories\Contracts\MessageRepositoryInterface;

class MarkMessagesAsSeenAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    public function handle(Conversation $conversation, string $userId): int
    {
        $updated = $this->messages->markAsSeen($conversation->id, $userId);

        if ($updated > 0) {
            broadcast(new MessagesSeen($conversation->id, $userId))->toOthers();
        }

        return $updated;
    }
}
