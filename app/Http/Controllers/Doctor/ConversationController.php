<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Chat / Conversations with patients
     */
    public function index()
    {
        $doctor = Auth::user();

        $conversations = Conversation::with(['patient', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('doctor.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $doctor = Auth::user();

        $conversation->load(['patient', 'messages' => fn ($q) => $q->oldest()]);

        // اعتبر رسائل المريض مقروءة
        $conversation->messages()
            ->where('sender_type', Patient::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('doctor.conversations.show', compact('conversation'));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $doctor = Auth::user();

        abort_unless($conversation->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $conversation->messages()->create([
            'sender_type' => User::class,
            'sender_id' => $doctor->id,
            'type' => 'text',
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return back();
    }

    public function deleteMessage(Conversation $conversation, Message $message)
    {
        $doctor = Auth::user();

        // تأكد إن المحادثة دي بتاعة الدكتور المسجل دخوله
        abort_unless($conversation->doctor_id === $doctor->id, 403);

        // تأكد إن الرسالة دي فعلاً تابعة للمحادثة دي
        abort_unless($message->conversation_id === $conversation->id, 404);

        // الدكتور يقدر يمسح بس رسايله هو، مش رسايل المريض
        abort_unless(
            $message->sender_type === User::class && $message->sender_id === $doctor->id,
            403,
            'لا يمكنك حذف رسائل المريض'
        );

        // حدّث آخر رسالة في المحادثة بعد الحذف
        $lastMessage = $conversation->messages()->latest()->first();
        $conversation->update([
            'last_message_at' => $lastMessage?->created_at,
        ]);

        $message->delete();

        return back()->with('success', 'تم حذف الرسالة');
    }
}
