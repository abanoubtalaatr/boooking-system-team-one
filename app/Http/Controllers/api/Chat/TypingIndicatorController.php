<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\ApiResponse;

/**
 * Broadcast-only, no DB write, no queued job — fires the ephemeral typing event
 * directly and returns 204.
 */
class TypingIndicatorController extends Controller
{
        use ApiResponse;

    public function __invoke(Request $request, Conversation $conversation): Response
    {
        $this->authorize("view", $conversation);

        broadcast(new UserTyping($conversation->id, $request->user()->id))->toOthers();

        return response()->noContent();
    }
}
