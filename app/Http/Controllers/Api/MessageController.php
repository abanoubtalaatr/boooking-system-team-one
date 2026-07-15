<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ListMessagesRequest;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private ChatService $chat)
    {
    }

    // GET /messages?conversation_id=1
    public function index(ListMessagesRequest $request)
    {
        $conversation = Conversation::findOrFail($request->conversation_id);
        $this->authorize('participate', $conversation);

        $messages = $this->chat->listMessages($conversation);

        return $this->success('تم جلب الرسائل', [
            'messages' => MessageResource::collection($messages),
        ]);
    }

    // POST /messages
    public function store(SendMessageRequest $request)
    {
        $conversation = Conversation::findOrFail($request->conversation_id);
        $sender = Auth::user();

        $message = $this->chat->send($conversation, $sender, $request->validated());

        return $this->created('تم إرسال الرسالة', [
            'message' => new MessageResource($message),
        ]);
    }

    // DELETE /messages/{message}
    public function destroy(Message $message)
    {
        $this->authorize('participate', $message->conversation);

        $this->chat->delete($message);

        return $this->success('تم حذف الرسالة', []);
    }

    // PUT /conversations/{conversation}/seen
    public function markSeen(Conversation $conversation)
    {
        $this->authorize('participate', $conversation);

        $updated = $this->chat->markRead($conversation, Auth::user());

        return $this->success('تم تحديث حالة القراءة', ['updated' => $updated]);
    }
}