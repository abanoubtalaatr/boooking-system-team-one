<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Chat;
use Illuminate\Support\Facades\Route;


Route::prefix("chat")->middleware(["auth:sanctum"])->group(function () {
    Route::get("conversations", [Chat\ConversationController::class, "index"]);
    Route::post("conversations", [Chat\ConversationController::class, "store"]);
    Route::get("conversations/{conversation}", [Chat\ConversationController::class, "show"]);
    Route::post("conversations/{conversation}/messages", [Chat\MessageController::class, "store"]);
    Route::delete("messages/{message}", [Chat\MessageController::class, "destroy"]);
    Route::put("conversations/{conversation}/seen", Chat\MarkAsSeenController::class);
    Route::post("conversations/{conversation}/typing", Chat\TypingIndicatorController::class);
});
