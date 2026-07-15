<?php

use App\Http\Controllers\Api\Booking\BookingController;
use App\Http\Controllers\Api\Doctor\DoctorDashboardController;
use App\Http\Controllers\Api\Doctor\DoctorPaymentIndexController;
use App\Http\Controllers\Api\Doctor\MarkCashCollectedController;
use App\Http\Controllers\Api\Doctor\StoreBookingNoShowReportController;
use App\Http\Controllers\Api\Faq\FaqController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Patient\PatientAuthController;
use App\Http\Controllers\Api\Patient\PatientPasswordResetController;
use App\Http\Controllers\Api\Payment\BookingCheckoutController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Payment\PaymobReturnController;
use App\Http\Controllers\Api\Payment\PaymobWebhookController;
use App\Http\Controllers\Api\Policy\PolicyController;
use App\Http\Controllers\Api\ReviewsController;
use App\Http\Controllers\Api\SearchHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [HomeController::class, 'index']);
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/privacy-policy', [PolicyController::class, 'privacy']);
Route::get('/terms', [PolicyController::class, 'terms']);

Route::apiResource('reviews', ReviewsController::class)->only(['index', 'show']);

Route::middleware('auth:patient')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites', [FavoriteController::class, 'destroy']);
    Route::apiResource('reviews', ReviewsController::class)->only(['store', 'update', 'destroy']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::post('/bookings/{booking}/checkout', BookingCheckoutController::class)->name('payments.checkout');
    Route::get('/payments/{payment}', PaymentController::class)->name('payments.show');
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search-history', [SearchHistoryController::class, 'index']);
    Route::delete('/search-history/{searchHistory}', [SearchHistoryController::class, 'destroy']);
});

Route::post('/webhooks/paymob', PaymobWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('payments.paymob.webhook');

Route::get('/payments/paymob/return', PaymobReturnController::class)
    ->name('payments.paymob.return');

Route::post('/doctor/bookings/{booking}/cash-collected', MarkCashCollectedController::class)
    ->middleware(['auth:sanctum', 'role:doctor'])
    ->name('doctor.payments.cash_collected');

Route::post('/doctor/bookings/{booking}/no-show-reports', StoreBookingNoShowReportController::class)
    ->middleware(['auth:sanctum', 'role:doctor'])
    ->name('doctor.no-show-reports.store');

Route::get('/doctor/dashboard', DoctorDashboardController::class)
    ->middleware(['auth:sanctum', 'role:doctor'])
    ->name('doctor.dashboard');

Route::get('/doctor/dashboard/payments', DoctorPaymentIndexController::class)
    ->middleware(['auth:sanctum', 'role:doctor'])
    ->name('doctor.dashboard.payments');

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

require __DIR__.'/doctor.php';
require __DIR__.'/chat.php';
require __DIR__.'/channels.php';
require __DIR__.'/admin_doctor.php';

if (file_exists(__DIR__.'/api_auth_additions.php')) {
    require __DIR__.'/api_auth_additions.php';
}
