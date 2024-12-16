<?php

use App\Core\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;



Route::middleware(['guest'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::get('/auth/signin', [AuthController::class, 'login'])->name('auth.show');
    Route::post('/auth/signin', [AuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/auth/register', [AuthController::class, 'storeUser'])->name('auth.store');
});



Route::get('/users/{id}', [HomeController::class, 'users']);

// Route::post('/test-ajax-csrf', [HomeController::class, 'test']);

require __DIR__ . '/auth.php';
