<?php

use App\Http\Controllers\Api\Faq\FaqController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Patient\PatientAuthController;
use App\Http\Controllers\Api\Patient\PatientPasswordResetController;
use App\Http\Controllers\Api\Policy\PolicyController;
use Illuminate\Support\Facades\Route;

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

    // Home, FAQ, and Policy routes
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::get('/privacy-policy', [PolicyController::class, 'privacy']);
    Route::get('/terms', [PolicyController::class, 'terms']);
