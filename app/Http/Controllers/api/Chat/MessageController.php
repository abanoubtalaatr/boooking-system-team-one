<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponse;

/**
 * Only store/destroy are used; index is folded into ConversationController@show.
 */
class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ChatService $chat)
    {
    }

    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $message = $this->chat->send(
            $conversation,
            $request->user()->id,
            $request->validated(),
            $request->file("file"),
        );

        return $this->apiResponse(new MessageResource($message), "Message sent.", 201);
    }

    public function destroy(Message $message): JsonResponse
    {
        $this->authorize("delete", $message);
        $this->chat->deleteMessage($message, auth()->id());

        return $this->apiResponse(null, "Message deleted.");
    }
}
