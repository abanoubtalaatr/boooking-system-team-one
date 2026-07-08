<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authId = $request->user()->id;
        $other = $this->otherParticipant($authId);

        return [
            "id" => $this->id,
            "other_participant" => [
                "id" => $other->id,
                "name" => $other->name,
            ],
            "last_message" => new MessageResource($this->whenLoaded("messages", fn () => $this->messages->first())),
            "unread_count" => $this->messages()
                ->where("status", "!=", "seen")
                ->where("sender_id", "!=", $authId)
                ->count(),
            "last_message_at" => $this->last_message_at,
        ];
    }
}
