<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;

class AdminConversationController extends Controller
{
    /**
     * Patients that chatted with this doctor
     */
    public function index(User $doctor)
    {
        abort_unless($doctor->role->value === 'doctor', 404);

        $conversations = Conversation::query()
            ->where('doctor_id', $doctor->id)
            ->with([
                'patient',
                'latestMessage',
            ])
            ->latest('last_message_at')
            ->paginate(10);

        return view(
            'admin.conversations.index',
            compact('doctor', 'conversations')
        );
    }

    /**
     * Show conversation messages
     */
    public function show(Conversation $conversation)
    {
        $conversation->load([
            'doctor',
            'patient',
        ]);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->paginate(20);

        return view(
            'admin.conversations.show',
            compact('conversation', 'messages')
        );
    }
}