<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Conversation;

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

    /**
     * يعتبر رسايل الطرف التاني مقروءة (مش رسايل القارئ نفسه).
     * بيرجع عدد الصفوف اللي اتحدثت.
     */
    public function markAsRead(Conversation $conversation, string $readerType, int $readerId): int
    {
        return Message::where('conversation_id', $conversation->id)
            ->where(function ($query) use ($readerType, $readerId) {
                $query->where('sender_type', '!=', $readerType)
                    ->orWhere('sender_id', '!=', $readerId);
            })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
