<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReviewsController;



Route::apiResource('reviews', ReviewsController::class);

