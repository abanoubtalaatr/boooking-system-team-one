<?php

namespace App\Events;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): array
    {
        return [new PresenceChannel("conversation." . $this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return "message.sent";
    }

    public function broadcastWith(): array
    {
        return (new MessageResource($this->message->load(["sender", "media"])))->resolve();
    }
}
