<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MessagesSeen implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public string $conversationId,
        public string $seenBy,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel("conversation." . $this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return "messages.seen";
    }

    public function broadcastWith(): array
    {
        return [
            "conversation_id" => $this->conversationId,
            "seen_by" => $this->seenBy,
            "seen_at" => now()->toIso8601String(),
        ];
    }
}
