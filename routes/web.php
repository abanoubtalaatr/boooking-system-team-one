<?php

use App\Http\Controllers\Admin\AdminConversationController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AvailabilitySlotController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\HospitalController;
use App\Http\Controllers\Admin\PatientFavoriteDoctorsController;
use App\Http\Controllers\Admin\PatientSearchHistoryController;
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
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/admin', AdminPaymentDashboardController::class)
        ->middleware(['active', 'role:admin|super-admin'])
        ->name('web.admin.dashboard');
    Route::get('/admin/withdrawals', [AdminWalletWithdrawalController::class, 'index'])
        ->middleware(['active', 'role:admin|super-admin', 'can:withdrawals.view'])
        ->name('web.admin.withdrawals.index');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/complete', [AdminWalletWithdrawalController::class, 'complete'])
        ->middleware(['active', 'role:admin|super-admin', 'can:withdrawals.complete'])
        ->name('web.admin.withdrawals.complete');
    Route::patch('/admin/withdrawals/{walletWithdrawal}/cancel', [AdminWalletWithdrawalController::class, 'cancel'])
        ->middleware(['active', 'role:admin|super-admin', 'can:withdrawals.cancel'])
        ->name('web.admin.withdrawals.cancel');
    Route::get('/admin/no-show-reports', [AdminNoShowReportController::class, 'index'])
        ->middleware(['active', 'role:admin|super-admin', 'can:no-show-reports.view'])
        ->name('web.admin.no-show-reports.index');
    Route::patch('/admin/no-show-reports/{bookingNoShowReport}/approve', [AdminNoShowReportController::class, 'approve'])
        ->middleware(['active', 'role:admin|super-admin', 'can:no-show-reports.approve'])
        ->name('web.admin.no-show-reports.approve');
    Route::patch('/admin/no-show-reports/{bookingNoShowReport}/reject', [AdminNoShowReportController::class, 'reject'])
        ->middleware(['active', 'role:admin|super-admin', 'can:no-show-reports.reject'])
        ->name('web.admin.no-show-reports.reject');

    Route::get('/doctor', DoctorPaymentDashboardController::class)
        ->middleware(['active', 'role:doctor'])
        ->name('web.doctor.dashboard');
    Route::get('/doctor/wallet', [DoctorWalletController::class, 'index'])
        ->middleware(['active', 'role:doctor'])
        ->name('web.doctor.wallet.index');
    Route::post('/doctor/wallet/withdrawals', [DoctorWalletController::class, 'store'])
        ->middleware(['active', 'role:doctor', 'throttle:10,1'])
        ->name('web.doctor.wallet.withdrawals.store');
    Route::get('/doctor/no-show-reports', [DoctorNoShowReportController::class, 'index'])
        ->middleware(['active', 'role:doctor'])
        ->name('web.doctor.no-show-reports.index');
    Route::post('/doctor/bookings/{booking}/no-show-reports', [DoctorNoShowReportController::class, 'store'])
        ->middleware(['active', 'role:doctor', 'throttle:10,1'])
        ->name('web.doctor.no-show-reports.store');
});

Route::middleware(['auth', 'active', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/no-access', 'admin.no-access')->name('no-access');
    Route::get('/dashboard', AdminPaymentDashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
    Route::get('/doctors', [DoctorController::class, 'index'])->middleware('can:doctors.view')->name('doctors');
    Route::get('/doctors/create', [DoctorController::class, 'create'])->middleware('can:doctors.create')->name('doctors.create');
    Route::post('/doctors', [DoctorController::class, 'store'])->middleware('can:doctors.create')->name('doctors.store');
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->middleware('can:doctors.view')->name('doctors.show');
    Route::get('/doctors/{doctor}/edit', [DoctorController::class, 'edit'])->middleware('can:doctors.update')->name('doctors.edit');
    Route::match(['put', 'patch'], '/doctors/{doctor}', [DoctorController::class, 'update'])->middleware('can:doctors.update')->name('doctors.update');
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy'])->middleware('can:doctors.suspend')->name('doctors.destroy');

    Route::get('/patients', [DashboardController::class, 'patients'])->middleware('can:patients.view')->name('patients');
    Route::get('/patients/{patient}', [DashboardController::class, 'patientProfile'])->middleware('can:patients.view')->name('patients.show');
    Route::get('/specialties', [DashboardController::class, 'specialties'])->middleware('can:specialties.view')->name('specialties');
    Route::get('/clinics', [DashboardController::class, 'clinics'])->middleware('can:clinics.view')->name('clinics');
    Route::get('/appointments', [DashboardController::class, 'appointments'])->middleware('can:appointments.view')->name('appointments');
    Route::get('/reports', [DashboardController::class, 'reports'])->middleware('can:reports.view')->name('reports');
    Route::get('/settings', [AdminPaymentSettingsController::class, 'edit'])->middleware('can:settings.view')->name('settings');
    Route::put('/settings', [AdminPaymentSettingsController::class, 'update'])->middleware('can:settings.update')->name('settings.update');

    Route::controller(AdminUserController::class)->prefix('users')->name('users.')->group(function (): void {
        Route::get('/', 'index')->middleware('can:admins.view')->name('index');
        Route::get('/create', 'create')->middleware('can:admins.create')->name('create');
        Route::post('/', 'store')->middleware('can:admins.create')->name('store');
        Route::get('/{admin}/edit', 'edit')->middleware('can:admins.view')->name('edit');
        Route::put('/{admin}', 'update')->middleware('can:admins.update')->name('update');
        Route::patch('/{admin}/status', 'updateStatus')->middleware('can:admins.status')->name('status');
        Route::delete('/{admin}', 'destroy')->middleware('can:admins.delete')->name('destroy');
        Route::put('/{admin}/permissions', 'updatePermissions')->middleware('can:admins.manage-permissions')->name('permissions');
    });

    Route::get('/patient-favorites', [PatientFavoriteDoctorsController::class, 'index'])->middleware('can:patients.favorites.view')->name('patient-favorites');
    Route::get('/patient-favorites/{patient}', [PatientFavoriteDoctorsController::class, 'show'])->middleware('can:patients.favorites.view')->name('patient-favorites.show');
    Route::get('/search-history', [PatientSearchHistoryController::class, 'index'])->middleware('can:patients.search-history.view')->name('search-history');
    Route::get('/search-history/{patient}', [PatientSearchHistoryController::class, 'show'])->middleware('can:patients.search-history.view')->name('search-history.show');

    Route::get('/hospitals', [HospitalController::class, 'index'])->middleware('can:clinics.view')->name('hospitals.index');
    Route::get('/hospitals/create', [HospitalController::class, 'create'])->middleware('can:clinics.create')->name('hospitals.create');
    Route::post('/hospitals', [HospitalController::class, 'store'])->middleware('can:clinics.create')->name('hospitals.store');
    Route::get('/hospitals/{hospital}', [HospitalController::class, 'show'])->middleware('can:clinics.view')->name('hospitals.show');
    Route::get('/hospitals/{hospital}/edit', [HospitalController::class, 'edit'])->middleware('can:clinics.update')->name('hospitals.edit');
    Route::match(['put', 'patch'], '/hospitals/{hospital}', [HospitalController::class, 'update'])->middleware('can:clinics.update')->name('hospitals.update');
    Route::delete('/hospitals/{hospital}', [HospitalController::class, 'destroy'])->middleware('can:clinics.delete')->name('hospitals.destroy');

    Route::get('/doctors/{doctor}/conversations', [AdminConversationController::class, 'index'])
        ->middleware('can:doctors.conversations.view')
        ->name('doctors.conversations');
    Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show'])
        ->middleware('can:doctors.conversations.view')
        ->name('conversations.show');

    Route::get('/availability-slots', [AvailabilitySlotController::class, 'index'])->middleware('can:appointments.view')->name('availability-slots.index');
    Route::get('/availability-slots/{availabilitySlot}', [AvailabilitySlotController::class, 'show'])->middleware('can:appointments.view')->name('availability-slots.show');

    Route::get('/bookings', [BookingController::class, 'index'])
        ->middleware('can:bookings.view')
        ->name('bookings');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])
        ->middleware('can:bookings.view')
        ->name('bookings.show');
});

Route::middleware(['auth', 'active', 'role:doctor'])
    ->prefix('doctor')
    ->name('doctor.')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
        Route::post('/conversations/{conversation}/send', [ConversationController::class, 'sendMessage'])->name('conversations.send');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::delete('/conversations/{conversation}/messages/{message}', [ConversationController::class, 'deleteMessage'])
            ->name('conversations.messages.destroy');

        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings', [DoctorDashboardController::class, 'bookings'])->name('bookings');
        Route::resource('availability-slots', DoctorAvailabilitySlotController::class);
        Route::get('/patients', [DoctorDashboardController::class, 'patients'])->name('patients');
        Route::get('/reviews', [DoctorDashboardController::class, 'reviews'])->name('reviews');
        Route::get('/profile', [DoctorDashboardController::class, 'profile'])->name('profile');
    });
