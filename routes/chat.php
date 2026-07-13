<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
 
// ضيف السطور دي جوه routes/api.php تحت middleware('auth:sanctum')
 
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
});
/*
Route::prefix("chat")->middleware(["auth:sanctum"])->group(function () {
    Route::get("conversations", [Chat\ConversationController::class, "index"]);
    Route::post("conversations", [Chat\ConversationController::class, "store"]);
    Route::get("conversations/{conversation}", [Chat\ConversationController::class, "show"]);
    Route::post("conversations/{conversation}/messages", [Chat\MessageController::class, "store"]);
    Route::delete("messages/{message}", [Chat\MessageController::class, "destroy"]);
    Route::put("conversations/{conversation}/seen", Chat\MarkAsSeenController::class);
    Route::post("conversations/{conversation}/typing", Chat\TypingIndicatorController::class);
});*/
