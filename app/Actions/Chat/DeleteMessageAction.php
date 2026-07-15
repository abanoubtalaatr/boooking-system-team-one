<?php

namespace App\Actions\Chat;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;

class DeleteMessageAction
{
    public function __construct(
        private MessageRepositoryInterface $messages,
    ) {}

    public function handle(Message $message): bool
    {
        return $this->messages->delete($message);
    }
}