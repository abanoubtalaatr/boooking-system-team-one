<?php

namespace App\Services\Web;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ConversationService
{
    public function paginateForDoctor(User $doctor, int $perPage = 15): LengthAwarePaginator
    {
        return Conversation::query()
            ->with(['patient', 'latestMessage'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_type', Patient::class)
                    ->whereNull('read_at'),
            ])
            ->whereBelongsTo($doctor, 'doctor')
            ->where('status', 'active')
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function ownedByDoctor(Conversation $conversation, User $doctor): bool
    {
        return (int) $conversation->doctor_id === (int) $doctor->id;
    }

    public function isActive(Conversation $conversation): bool
    {
        return $conversation->status === 'active';
    }

    /**
     * Mark every unread message sent by the patient as read.
     */
    public function markAsReadByPatient(Conversation $conversation): void
    {
        $conversation->messages()
            ->where('sender_type', Patient::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function createMessage(Conversation $conversation, User $doctor, array $data, ?UploadedFile $attachment): Message
    {
        $message = $conversation->messages()->create([
            'sender_type' => User::class,
            'sender_id'   => $doctor->id,
            'type'        => $this->resolveMessageType($attachment),
            'body'        => $data['body'] ?? null,
        ]);

        if ($attachment) {
            $message->addMedia($attachment)->toMediaCollection('attachment');
        }

        $conversation->update(['last_message_at' => now()]);

        return $message->load('media');
    }

    public function messageBelongsToDoctor(Message $message, User $doctor): bool
    {
        return $message->sender_type === User::class
            && (int) $message->sender_id === (int) $doctor->id;
    }

    public function deleteMessage(Conversation $conversation, Message $message): void
    {
        $message->delete();

        $conversation->update([
            'last_message_at' => $conversation->messages()->reorder()->latest()->value('created_at'),
        ]);
    }

    protected function resolveMessageType(?UploadedFile $attachment): string
    {
        if (! $attachment) {
            return 'text';
        }

        $mime = (string) $attachment->getMimeType();

        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'audio/') => 'voice',
            default => 'file',
        };
    }
}