<?php

use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PatientFavoriteDoctorsController;
use App\Http\Controllers\Admin\PatientSearchHistoryController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\HospitalController;
use App\Http\Controllers\Admin\AvailabilitySlotController;
use App\Http\Controllers\Doctor\AvailabilitySlotController as DoctorAvailabilitySlotController;
use App\Http\Controllers\Doctor\ConversationController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Web\AdminNoShowReportController;
use App\Http\Controllers\Web\AdminPaymentDashboardController;
use App\Http\Controllers\Web\AdminPaymentSettingsController;
use App\Http\Controllers\Web\AdminWalletWithdrawalController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\DoctorNoShowReportController;
use App\Http\Controllers\Web\DoctorPaymentDashboardController;
use App\Http\Controllers\Web\DoctorWalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/admin', AdminPaymentDashboardController::class)->middleware('role:admin')->name('web.admin.dashboard');
    Route::get('/admin/settings/payments', [AdminPaymentSettingsController::class, 'edit'])->middleware('role:admin')->name('web.admin.payment-settings.edit');
    Route::put('/admin/settings/payments', [AdminPaymentSettingsController::class, 'update'])->middleware('role:admin')->name('web.admin.payment-settings.update');
    Route::get('/admin/withdrawals', [AdminWalletWithdrawalController::class, 'index'])->middleware('role:admin')->name('web.admin.withdrawals.index');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/complete', [AdminWalletWithdrawalController::class, 'complete'])->middleware('role:admin')->name('web.admin.withdrawals.complete');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/cancel', [AdminWalletWithdrawalController::class, 'cancel'])->middleware('role:admin')->name('web.admin.withdrawals.cancel');
    Route::get('/admin/no-show-reports', [AdminNoShowReportController::class, 'index'])->middleware('role:admin')->name('web.admin.no-show-reports.index');
    Route::patch('/admin/no-show-reports/{bookingNoShowReport}/approve', [AdminNoShowReportController::class, 'approve'])->middleware('role:admin')->name('web.admin.no-show-reports.approve');
    Route::patch('/admin/no-show-reports/{bookingNoShowReport}/reject', [AdminNoShowReportController::class, 'reject'])->middleware('role:admin')->name('web.admin.no-show-reports.reject');
    Route::get('/doctor', DoctorPaymentDashboardController::class)->middleware('role:doctor')->name('web.doctor.dashboard');
    Route::get('/doctor/wallet', [DoctorWalletController::class, 'index'])->middleware('role:doctor')->name('web.doctor.wallet.index');
    Route::post('/doctor/wallet/withdrawals', [DoctorWalletController::class, 'store'])->middleware(['role:doctor', 'throttle:10,1'])->name('web.doctor.wallet.withdrawals.store');
    Route::get('/doctor/no-show-reports', [DoctorNoShowReportController::class, 'index'])->middleware('role:doctor')->name('web.doctor.no-show-reports.index');
    Route::post('/doctor/bookings/{booking}/no-show-reports', [DoctorNoShowReportController::class, 'store'])->middleware(['role:doctor', 'throttle:10,1'])->name('web.doctor.no-show-reports.store');

});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/patients', [DashboardController::class, 'patients'])->name('patients');
    Route::get('/specialties', [DashboardController::class, 'specialties'])->name('specialties');
    Route::get('/appointments', [DashboardController::class, 'appointments'])->name('appointments');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

    Route::get('/patient-favorites', [PatientFavoriteDoctorsController::class, 'index'])->name('patient-favorites');
    Route::get('/patient-favorites/{patient}', [PatientFavoriteDoctorsController::class, 'show'])->name('patient-favorites.show');
    Route::get('/search-history', [PatientSearchHistoryController::class, 'index'])->name('search-history');
    Route::get('/search-history/{patient}', [PatientSearchHistoryController::class, 'show'])->name('search-history.show');
    
    Route::resource('hospitals', HospitalController::class);
    Route::resource('doctors', DoctorController::class);
    Route::get('/doctors/{doctor}/conversations', [AdminConversationController::class, 'index'])->name('doctors.conversations');
    Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show'])->name('conversations.show');
    Route::get('availability-slots', [AvailabilitySlotController::class, 'index'])->name('availability-slots.index');
    Route::get('availability-slots/{availabilitySlot}', [AvailabilitySlotController::class, 'show'])->name('availability-slots.show');
    
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->scopeBindings()->group(function (): void {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
    Route::post('/conversations/{conversation}/send', [ConversationController::class, 'sendMessage'])->name('conversations.send');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::delete('/conversations/{conversation}/messages/{message}', [ConversationController::class, 'deleteMessage'])->name('conversations.messages.destroy');
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [DoctorDashboardController::class, 'bookings'])->name('bookings');
    Route::resource('availability-slots', DoctorAvailabilitySlotController::class);
    Route::get('/patients', [DoctorDashboardController::class, 'patients'])->name('patients');
    Route::get('/reviews', [DoctorDashboardController::class, 'reviews'])->name('reviews');
    Route::get('/profile', [DoctorDashboardController::class, 'profile'])->name('profile');

});
