<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminConversationController extends Controller
{
    private const CONVERSATIONS_PER_PAGE = 10;
    private const MESSAGES_PER_PAGE = 20;

    /**
     * Patients that chatted with this doctor.
     */
    public function index(User $doctor): View
    {
        // {doctor} is a plain User route-model-bind, so any user id works
        // here unless we confirm the role — reject anything that isn't a doctor.
        abort_unless($doctor->role->value === 'doctor', 404);

        $conversations = Conversation::query()
            ->where('doctor_id', $doctor->id)
            ->with(['patient', 'latestMessage'])
            ->latest('last_message_at')
            ->paginate(self::CONVERSATIONS_PER_PAGE);

        return view(
            'admin.conversations.index',
            compact('doctor', 'conversations')
        );
    }

    /**
     * Show conversation messages.
     */
    public function show(Conversation $conversation): View
    {
        $conversation->load([
            'doctor',
            'patient',
        ]);

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->paginate(self::MESSAGES_PER_PAGE);

        return view(
            'admin.conversations.show',
            compact('conversation', 'messages')
        );
    }
}