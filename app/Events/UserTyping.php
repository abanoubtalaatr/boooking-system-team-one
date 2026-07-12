<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Ephemeral, broadcast-only — never queued, never persisted.
 */
class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $conversationId,
        public string $userId,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel("conversation." . $this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return "user.typing";
    }

    public function broadcastWith(): array
    {
        return [
            "conversation_id" => $this->conversationId,
            "user_id" => $this->userId,
        ];
    }
}
