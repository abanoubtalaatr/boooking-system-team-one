<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('no-show-reports', Admin\BookingNoShowReportIndexController::class)
        ->name('admin.no-show-reports.index');
    Route::patch('no-show-reports/{bookingNoShowReport}/approve', Admin\ApproveBookingNoShowReportController::class)
        ->name('admin.no-show-reports.approve');
    Route::patch('no-show-reports/{bookingNoShowReport}/reject', Admin\RejectBookingNoShowReportController::class)
        ->name('admin.no-show-reports.reject');
    Route::get('payments', Admin\AdminPaymentIndexController::class)->name('admin.payments.index');
    Route::apiResource('doctors', Admin\DoctorController::class);
    Route::apiResource('specialties', Admin\SpecialtyController::class);
    Route::apiResource('hospitals', Admin\HospitalController::class);
    Route::put('doctors/{doctor}/approve', Admin\ApproveDoctorController::class);
    Route::put('doctors/{doctor}/suspend', Admin\SuspendDoctorController::class);
});
