<?php

namespace App\Policies;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Model;

class ConversationPolicy
{
    public function participate(Model $user, Conversation $conversation): bool
    {
        $type = $user instanceof \App\Models\Patient ? 'patient' : 'doctor';

        return $conversation->hasParticipant($type, $user->id);
    }
}
