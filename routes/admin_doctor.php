<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'active', 'role:admin|super-admin'])->group(function () {
    Route::get('no-show-reports', Admin\BookingNoShowReportIndexController::class)
        ->middleware('can:no-show-reports.view')
        ->name('admin.no-show-reports.index');
    Route::patch('no-show-reports/{bookingNoShowReport}/approve', Admin\ApproveBookingNoShowReportController::class)
        ->middleware('can:no-show-reports.approve')
        ->name('admin.no-show-reports.approve');
    Route::patch('no-show-reports/{bookingNoShowReport}/reject', Admin\RejectBookingNoShowReportController::class)
        ->middleware('can:no-show-reports.reject')
        ->name('admin.no-show-reports.reject');
    Route::get('payments', Admin\AdminPaymentIndexController::class)->middleware('can:dashboard.view')->name('admin.payments.index');

    Route::get('doctors', [Admin\DoctorController::class, 'index'])->middleware('can:doctors.view')->name('doctors.index');
    Route::post('doctors', [Admin\DoctorController::class, 'store'])->middleware('can:doctors.create')->name('doctors.store');
    Route::get('doctors/{doctor}', [Admin\DoctorController::class, 'show'])->middleware('can:doctors.view')->name('doctors.show');
    Route::match(['put', 'patch'], 'doctors/{doctor}', [Admin\DoctorController::class, 'update'])->middleware('can:doctors.update')->name('doctors.update');
    Route::delete('doctors/{doctor}', [Admin\DoctorController::class, 'destroy'])->middleware('can:doctors.suspend')->name('doctors.destroy');
    Route::put('doctors/{doctor}/approve', Admin\ApproveDoctorController::class)->middleware('can:doctors.approve');
    Route::put('doctors/{doctor}/suspend', Admin\SuspendDoctorController::class)->middleware('can:doctors.suspend');

    Route::get('specialties', [Admin\SpecialtyController::class, 'index'])->middleware('can:specialties.view')->name('specialties.index');
    Route::post('specialties', [Admin\SpecialtyController::class, 'store'])->middleware('can:specialties.create')->name('specialties.store');
    Route::get('specialties/{specialty}', [Admin\SpecialtyController::class, 'show'])->middleware('can:specialties.view')->name('specialties.show');
    Route::match(['put', 'patch'], 'specialties/{specialty}', [Admin\SpecialtyController::class, 'update'])->middleware('can:specialties.update')->name('specialties.update');
    Route::delete('specialties/{specialty}', [Admin\SpecialtyController::class, 'destroy'])->middleware('can:specialties.delete')->name('specialties.destroy');

    Route::get('hospitals', [Admin\HospitalController::class, 'index'])->middleware('can:clinics.view')->name('hospitals.index');
    Route::post('hospitals', [Admin\HospitalController::class, 'store'])->middleware('can:clinics.create')->name('hospitals.store');
    Route::get('hospitals/{hospital}', [Admin\HospitalController::class, 'show'])->middleware('can:clinics.view')->name('hospitals.show');
    Route::match(['put', 'patch'], 'hospitals/{hospital}', [Admin\HospitalController::class, 'update'])->middleware('can:clinics.update')->name('hospitals.update');
    Route::delete('hospitals/{hospital}', [Admin\HospitalController::class, 'destroy'])->middleware('can:clinics.delete')->name('hospitals.destroy');
});
