<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('payments', Admin\AdminPaymentIndexController::class)->name('admin.payments.index');
    Route::apiResource('doctors', Admin\DoctorController::class);
    Route::apiResource('specialties', Admin\SpecialtyController::class);
    Route::apiResource('hospitals', Admin\HospitalController::class);
    Route::put('doctors/{doctor}/approve', Admin\ApproveDoctorController::class);
    Route::put('doctors/{doctor}/suspend', Admin\SuspendDoctorController::class);
});
