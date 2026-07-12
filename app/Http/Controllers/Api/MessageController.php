<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    // GET /messages?conversation_id=1
    public function index(Request $request)
    {
        $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        $this->authorizeParticipant($conversation);

        $messages = $conversation->messages()
            ->with('media')
            ->paginate(30);

        return response()->json($messages);
    }

    // POST /messages
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'type' => ['required', Rule::in(['text', 'voice', 'image', 'file'])],
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:20480'], // 20MB
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);
        $this->authorizeParticipant($conversation);

        if ($validated['type'] === 'text' && empty($validated['body'])) {
            return response()->json(['message' => 'body مطلوب لرسالة نصية'], 422);
        }

        if ($validated['type'] !== 'text' && ! $request->hasFile('attachment')) {
            return response()->json(['message' => 'attachment مطلوب لنوع الرسالة ده'], 422);
        }

        $sender = Auth::user();

        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'type' => $validated['type'],
            'body' => $validated['body'] ?? null,
        ]);

        if ($request->hasFile('attachment')) {
            $message->addMediaFromRequest('attachment')
                ->toMediaCollection('attachment');
        }

        $conversation->update(['last_message_at' => now()]);

        $message->load('media');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    // DELETE /messages/{message}
    public function destroy(Message $message)
    {
        $this->authorizeParticipant($message->conversation);

        $message->delete();

        return response()->json(['message' => 'تم حذف الرسالة'], 200);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        $user = Auth::user();
        $type = $user instanceof \App\Models\Patient ? 'patient' : 'doctor';

        abort_unless($conversation->hasParticipant($type, $user->id), 403);
    }
}