<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->name('login');
Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
Route::view('/doctor', 'doctor.dashboard')->name('doctor.dashboard');
