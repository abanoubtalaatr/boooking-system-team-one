<?php

use App\Http\Controllers\Doctor\ConversationController;
use App\Http\Controllers\Web\AdminPaymentDashboardController;
use App\Http\Controllers\Web\AdminPaymentSettingsController;
use App\Http\Controllers\Web\AdminWalletWithdrawalController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\DoctorPaymentDashboardController;
use App\Http\Controllers\Web\DoctorWalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/admin', AdminPaymentDashboardController::class)
        ->middleware('role:admin')
        ->name('web.admin.dashboard');

    Route::get('/admin/settings/payments', [AdminPaymentSettingsController::class, 'edit'])
        ->middleware('role:admin')
        ->name('web.admin.payment-settings.edit');
    Route::put('/admin/settings/payments', [AdminPaymentSettingsController::class, 'update'])
        ->middleware('role:admin')
        ->name('web.admin.payment-settings.update');
    Route::get('/admin/withdrawals', [AdminWalletWithdrawalController::class, 'index'])
        ->middleware('role:admin')
        ->name('web.admin.withdrawals.index');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/complete', [AdminWalletWithdrawalController::class, 'complete'])
        ->middleware('role:admin')
        ->name('web.admin.withdrawals.complete');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/cancel', [AdminWalletWithdrawalController::class, 'cancel'])
        ->middleware('role:admin')
        ->name('web.admin.withdrawals.cancel');

    Route::get('/doctor', DoctorPaymentDashboardController::class)
        ->middleware('role:doctor')
        ->name('web.doctor.dashboard');
    Route::get('/doctor/wallet', [DoctorWalletController::class, 'index'])
        ->middleware('role:doctor')
        ->name('web.doctor.wallet.index');
    Route::post('/doctor/wallet/withdrawals', [DoctorWalletController::class, 'store'])
        ->middleware(['role:doctor', 'throttle:10,1'])
        ->name('web.doctor.wallet.withdrawals.store');
});

Route::middleware(['auth', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
        Route::post('/conversations/{conversation}/send', [ConversationController::class, 'sendMessage'])->name('conversations.send');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::delete('/conversations/{conversation}/messages/{message}', [ConversationController::class, 'deleteMessage'])
            ->name('conversations.messages.destroy');
    });
