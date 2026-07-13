<?php

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface
{
    public function find(string $id): ?Conversation;

    public function findBetween(string $patientId, string $doctorId): ?Conversation;

    public function create(array $data): Conversation;

    public function touchLastMessageAt(Conversation $conversation): void;

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator;
}
