<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Doctor\ConversationController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;

// صفحات تسجيل الدخول
Route::view('/', 'auth.login')->name('login');
//Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');// should be auth
//Route::view('/doctor', 'doctor.dashboard')->name('doctor.dashboard');// should be auth
//Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware(['auth'])->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/doctor', 'doctor.dashboard')->name('doctor.dashboard');

});

// مجموعة الأدمن
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
    Route::get('/doctors', [DashboardController::class, 'doctors'])->name('doctors');
    Route::get('/patients', [DashboardController::class, 'patients'])->name('patients');
    Route::get('/specialties', [DashboardController::class, 'specialties'])->name('specialties');
    Route::get('/clinics', [DashboardController::class, 'clinics'])->name('clinics');
    Route::get('/appointments', [DashboardController::class, 'appointments'])->name('appointments');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/users', [DashboardController::class, 'users'])->name('users');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');

    Route::get('/bookings', [BookingController::class, 'index'])
        ->name('bookings');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])
        ->name('bookings.show');
});

// مجموعة الطبيب
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations');
    Route::post('/conversations/{conversation}/send', [ConversationController::class, 'sendMessage'])->name('conversations.send');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::delete('/conversations/{conversation}/messages/{message}', [ConversationController::class, 'deleteMessage'])
    ->name('conversations.messages.destroy');
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    // Route::get('/bookings', [DoctorDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/schedule', [DoctorDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/patients', [DoctorDashboardController::class, 'patients'])->name('patients');
    Route::get('/reviews', [DoctorDashboardController::class, 'reviews'])->name('reviews');
    Route::get('/profile', [DoctorDashboardController::class, 'profile'])->name('profile');

    // Route::get('/bookings', [BookingController::class, 'index'])
    //     ->name('bookings');
    // Route::get('/bookings/{booking}', [BookingController::class, 'show'])
    //     ->name('bookings.show');
});

// Route::prefix('dashboard')->middleware('auth')->group(function () {

//     Route::get('/bookings', [BookingController::class, 'index'])
//         ->name('dashboard.bookings');

//     Route::get('/bookings/{booking}', [BookingController::class, 'show'])
//         ->name('dashboard.bookings.show');
// });


