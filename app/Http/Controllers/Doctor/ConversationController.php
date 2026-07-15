<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\Patient;
use App\Models\Message;
use App\Models\User;
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

        $conversation->load(['patient', 'messages' => fn ($q) => $q->oldest()->with('media')]);

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

        // التحقق من المدخلات (النص أو الملف المرفق)
        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:20480', // حد أقصى 20 ميجا
        ]);

        // تحديد نوع الرسالة تلقائياً بناءً على المرفقات
        $type = 'text';
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();

            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'audio/')) {
                $type = 'voice';
            } else {
                $type = 'file';
            }
        }

        // التحقق من أنه لا توجد رسالة فارغة بدون مرفقات
        if ($type === 'text' && empty($validated['body'])) {
            return back()->withErrors(['body' => 'الرجاء كتابة رسالة أو إرفاق ملف.']);
        }

        // إنشاء الرسالة
        $message = $conversation->messages()->create([
            'sender_type' => get_class($doctor),
            'sender_id'   => $doctor->id,
            'type'        => $type,
            'body'        => $validated['body'] ?? null,
        ]);

        // رفع الملف إذا وُجد باستخدام Spatie Media Library
        if ($request->hasFile('attachment')) {
            $message->addMediaFromRequest('attachment')
                ->toMediaCollection('attachment');
        }

        $conversation->update(['last_message_at' => now()]);

        // تحميل الـ Media للتأكد من بث الرابط الصحيح للمستمعين
        $message->load('media');

        // ✅ بث الرسالة فوراً ليعمل الـ Real-Time
        broadcast(new \App\Events\MessageSent($message))->toOthers();

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
