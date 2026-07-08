<?php

/*
|--------------------------------------------------------------------------
| Doctor & Chat module routes — append to routes/api.php
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Api\Doctor;
use Illuminate\Support\Facades\Route;



Route::prefix("doctor")->middleware(["auth:sanctum", "role:doctor"])->group(function () {
    Route::get("profile", [Doctor\ProfileController::class, "show"]);
    Route::put("profile", [Doctor\ProfileController::class, "update"]);
    Route::apiResource("availability-slots", Doctor\AvailabilitySlotController::class);
    Route::put("specialties", Doctor\AssignSpecialtiesController::class);
    Route::put("hospitals", Doctor\AssignHospitalsController::class);
    Route::put("bookings/{booking}/accept", Doctor\AcceptBookingController::class);
    Route::put("bookings/{booking}/reject", Doctor\RejectBookingController::class);
});


