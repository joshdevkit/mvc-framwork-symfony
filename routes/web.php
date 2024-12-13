<?php

use App\Core\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/signin', [HomeController::class, 'login']);

Route::post('/signup', [HomeController::class, 'storeUser']);
Route::get('/users/{id}', [HomeController::class, 'users']);


require __DIR__ . '/auth.php';
