<?php

use  App\Core\Route;
use App\Http\Controllers\Auth\AuthController;



Route::middleware(['auth'])->group(function () {
    Route::get('/account', [AuthController::class, 'profile']);
    Route::post('/account/update', [AuthController::class, 'update']);
    Route::post('/account/update-avatar', [AuthController::class, 'update_avatar']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
