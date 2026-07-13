<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function find(string $id): ?Message;

    public function create(array $data): Message;

    public function delete(Message $message): bool;

    public function paginateForConversation(string $conversationId, int $perPage = 30): LengthAwarePaginator;

    /** Marks the other participants messages as seen; returns number of rows updated. */
    public function markAsSeen(string $conversationId, string $userId): int;
}
