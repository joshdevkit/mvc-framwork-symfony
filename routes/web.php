<?php

use App\Core\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
// Route::get('/users/{id}', [HomeController::class, 'users']);


Route::middleware(['guest'])->group(function () {
    Route::get('/signup', [AuthController::class, 'register']);
    Route::get('/signin', [AuthController::class, 'login']);
    Route::post('/signin', [AuthController::class, 'authenticate']);
    Route::post('/signup', [AuthController::class, 'storeUser']);
});

require __DIR__ . '/auth.php';
