<?php

/*
|--------------------------------------------------------------------------
| Auth route — append to routes/api.php, OUTSIDE any auth:sanctum group
|--------------------------------------------------------------------------
| This must NOT sit inside a ->middleware(['auth:sanctum', ...]) group, or
| Postman will get a 401 before it ever reaches the controller (you need to
| be unauthenticated to log in and get your first token).
*/

use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::post("login", LoginController::class);
