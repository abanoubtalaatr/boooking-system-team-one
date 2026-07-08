<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class MarkAsSeenController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ChatService $chat)
    {
    }

    public function __invoke(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize("view", $conversation);
        $updated = $this->chat->markSeen($conversation, $request->user()->id);

        return $this->apiResponse(["updated" => $updated], "Marked as seen.");
    }
}
