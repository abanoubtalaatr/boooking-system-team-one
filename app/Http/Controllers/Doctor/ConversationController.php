<?php

namespace App\Http\Controllers\Doctor;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $request->user();

        $conversations = Conversation::query()
            ->with(['patient', 'latestMessage'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_type', Patient::class)
                    ->whereNull('read_at'),
            ])
            ->whereBelongsTo($doctor, 'doctor')
            ->where('status', 'active')
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('doctor.conversations.index', compact('conversations'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        abort_unless((int) $conversation->doctor_id === (int) $request->user()->id, 403);

        $conversation->load(['patient', 'messages' => fn ($q) => $q->oldest()->with('media')]);

        $conversation->messages()
            ->where('sender_type', Patient::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('doctor.conversations.show', compact('conversation'));
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $doctor = $request->user();

        abort_unless((int) $conversation->doctor_id === (int) $doctor->id, 403);
        abort_unless($conversation->status === 'active', 409);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        $type = 'text';

        if ($request->hasFile('attachment')) {
            $mime = (string) $request->file('attachment')->getMimeType();

            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'audio/')) {
                $type = 'voice';
            } else {
                $type = 'file';
            }
        }

        if ($type === 'text' && empty($validated['body'])) {
            return back()->withErrors(['body' => 'الرجاء كتابة رسالة أو إرفاق ملف.']);
        }

        $message = $conversation->messages()->create([
            'sender_type' => User::class,
            'sender_id' => $doctor->id,
            'type' => $type,
            'body' => $validated['body'] ?? null,
        ]);

        if ($request->hasFile('attachment')) {
            $message->addMediaFromRequest('attachment')
                ->toMediaCollection('attachment');
        }

        $conversation->update(['last_message_at' => now()]);

        $message->load('media');

        broadcast(new MessageSent($message))->toOthers();

        return back();
    }

    public function deleteMessage(Request $request, Conversation $conversation, Message $message): RedirectResponse
    {
        $doctor = $request->user();

        abort_unless((int) $conversation->doctor_id === (int) $doctor->id, 403);

        abort_unless(
            $message->sender_type === User::class && (int) $message->sender_id === (int) $doctor->id,
            403,
            'لا يمكنك حذف رسائل المريض'
        );

        $message->delete();
        $conversation->update([
            'last_message_at' => $conversation->messages()->reorder()->latest()->value('created_at'),
        ]);

        return back()->with('success', 'تم حذف الرسالة');
    }
}
