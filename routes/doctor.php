<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\AvailabilitySlotController;


Route::middleware('auth:patient')->prefix('doctors')->group(function () {

    // doctors
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/{id}', [DoctorController::class, 'show']);

    // available slots
    Route::get('/{doctor}/availability-slots', [AvailabilitySlotController::class, 'index']);
    Route::get('/availability-slots/{id}', [AvailabilitySlotController::class, 'show']);
});


