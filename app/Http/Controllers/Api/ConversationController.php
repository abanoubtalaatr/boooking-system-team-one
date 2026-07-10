<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ConversationController extends Controller
{
    // GET /conversations
    public function index()
    {
        $user = Auth::user();
        $column = $user instanceof \App\Models\Patient ? 'patient_id' : 'doctor_id';

        $conversations = Conversation::where($column, $user->id)
            ->with(['latestMessage', 'patient', 'doctor'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return response()->json($conversations);
    }
}