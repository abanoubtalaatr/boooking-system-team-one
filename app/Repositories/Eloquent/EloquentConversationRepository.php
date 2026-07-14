<?php

namespace App\Repositories\Eloquent;

use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    public function find(string $id): ?Conversation
    {
        return Conversation::with(["patient", "doctor"])->find($id);
    }

    public function findBetween(string $patientId, string $doctorId): ?Conversation
    {
        return Conversation::where("patient_id", $patientId)
            ->where("doctor_id", $doctorId)
            ->first();
    }

    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }

    public function touchLastMessageAt(Conversation $conversation): void
    {
        $conversation->update(["last_message_at" => now()]);
    }

    public function paginateForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Conversation::with(["patient", "doctor"])
            ->where("patient_id", $userId)
            ->orWhere("doctor_id", $userId)
            ->orderByDesc("last_message_at")
            ->paginate($perPage);
    }
}
