<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    // عدّل الشرط ده حسب طريقة تحديدك لو اليوزر الحالي مريض أو دكتور
    // (مثال: لو عندك guard منفصل للمريض، أو عمود type على جدول users)
    if ($user instanceof \App\Models\Patient) {
        return $conversation->patient_id === $user->id;
    }

    return $conversation->doctor_id === $user->id;
});