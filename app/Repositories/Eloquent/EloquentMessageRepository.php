<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function find(string $id): ?Message
    {
        return Message::with(["sender", "media"])->find($id);
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function delete(Message $message): bool
    {
        return (bool) $message->delete();
    }

    public function paginateForConversation(string $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::with(["sender", "media"])
            ->where("conversation_id", $conversationId)
            ->orderByDesc("created_at")
            ->paginate($perPage);
    }

    public function markAsSeen(string $conversationId, string $userId): int
    {
        return Message::where("conversation_id", $conversationId)
            ->where("sender_id", "!=", $userId)
            ->where("status", "!=", "seen")
            ->update(["status" => "seen"]);
    }
}
