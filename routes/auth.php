<?php

use  App\Core\Route;
use App\Http\Controllers\Auth\AuthController;



Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
