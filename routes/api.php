<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\SearchHistoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Patient\PatientAuthController;
use App\Http\Controllers\Api\Patient\PatientPasswordResetController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('patient')
    ->name('patient.')
    ->group(function (): void {
        Route::post('register', [PatientAuthController::class, 'register'])
            ->middleware('throttle:6,1')
            ->name('register');

        Route::post('verify-otp', [PatientAuthController::class, 'verifyOtp'])
            ->middleware('throttle:6,1')
            ->name('verify-otp');

        Route::post('resend-otp', [PatientAuthController::class, 'resendOtp'])
            ->middleware('throttle:3,1')
            ->name('resend-otp');

        Route::post('login', [PatientAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');

        Route::post('forgot-password', [PatientPasswordResetController::class, 'forgotPassword'])
            ->middleware('throttle:3,1')
            ->name('forgot-password');

        Route::post('reset-password', [PatientPasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:6,1')
            ->name('reset-password');

        Route::post('logout', [PatientAuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('logout');
    });


// Favorites & Search History Routes: 
// ---------------------------------
Route::middleware('auth:sanctum')->group( function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites', [FavoriteController::class, 'destroy']);

    // Search history: (get, delete):     
    Route::get('/search-history', [SearchHistoryController::class, 'index']);
    Route::delete('/search-history/{searchHistory}', [SearchHistoryController::class, 'destroy']);
});