<?php

namespace App\Actions\Chat;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteMessageAction
{
    public function __construct(
        private readonly MessageRepositoryInterface $messages,
    ) {
    }

    public function handle(Message $message, string $requesterId): bool
    {
        if ($message->sender_id !== $requesterId) {
            throw new AuthorizationException("You can only delete your own messages.");
        }

        return $this->messages->delete($message);
    }
}
