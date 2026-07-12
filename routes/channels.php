<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
| Presence channel per conversation. Only the conversations patient_id
| or doctor_id may join — gives online-status for free via here()/joining()/leaving().
*/
Broadcast::channel("conversation.{conversationId}", function ($user, string $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    if ($user->id !== $conversation->patient_id && $user->id !== $conversation->doctor_id) {
        return false;
    }

    return ["id" => $user->id, "name" => $user->name];
});
