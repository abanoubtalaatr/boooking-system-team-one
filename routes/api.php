<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\SearchHistoryController;
use App\Http\Controllers\Api\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group( function () {

    // favorites: (get, add to favorites, remove from favorites, toggle favorites) 
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{favorite}', [FavoriteController::class, 'destroy']);

    // Search history: (get, delete): 
    Route::get('/search-history', [SearchHistoryController::class, 'index']);
    Route::delete('/search-history/{searchHistory}', [SearchHistoryController::class, 'destroy']);
});