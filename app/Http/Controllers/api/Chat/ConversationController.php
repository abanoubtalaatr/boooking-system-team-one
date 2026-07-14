<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

/**
 * Resource controller, only 3 of 5 verbs used:
 * - index: list my conversations
 * - store: start a conversation (idempotent, delegates to StartConversationAction)
 * - show: paginated messages for a conversation (folds Chat\MessageController@index in,
 *   avoiding a redundant "list messages" endpoint)
 * - update/destroy: n/a, a conversation is never edited or deleted directly -> 405
 */
class ConversationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ChatService $chat)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->chat->listForUser($request->user()->id);

        return $this->apiResponse(ConversationResource::collection($conversations));
    }

    public function store(StartConversationRequest $request): JsonResponse
    {
        $conversation = $this->chat->start($request->user()->id, $request->validated()["doctor_id"]);

        return $this->apiResponse(new ConversationResource($conversation), "Conversation ready.", 201);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize("view", $conversation);
        $messages = $this->chat->messagesFor($conversation->id);

        return $this->apiResponse(MessageResource::collection($messages));
    }

    public function update(): JsonResponse
    {
        return $this->errorResponse("Not supported.", 405);
    }

    public function destroy(): JsonResponse
    {
        return $this->errorResponse("Not supported.", 405);
    }
}
