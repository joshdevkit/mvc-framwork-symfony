<?php

use  App\Core\Route;
use App\Http\Controllers\Auth\AuthController;



Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'update']);
    Route::post('/profile/update-avatar', [AuthController::class, 'update_avatar']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
