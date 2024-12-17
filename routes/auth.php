<?php

use App\Core\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\DashboardController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/account', [AuthController::class, 'profile'])->name('profile');
    Route::post('/account/update', [AuthController::class, 'update'])->name('update-profile');
    Route::post('/account/update-avatar', [AuthController::class, 'update_avatar'])->name('update-avatar');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
