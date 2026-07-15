<?php

use Illuminate\Support\Facades\Broadcast;
/*
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});*/

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // جلب المحادثة للتأكد أن المستخدم (الدكتور) مصرح له بدخولها
    $conversation = \App\Models\Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }

    // هنا نتأكد أن الـ user_id الخاص بالدكتور يطابق doctor_id في المحادثة
    return (int) $user->id === (int) $conversation->doctor_id;
});
