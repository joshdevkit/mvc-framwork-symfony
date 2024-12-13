<?php

use App\Core\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/signin', [HomeController::class, 'login']);
Route::post('/signin', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/signup', [HomeController::class, 'register']);
Route::post('/signup', [HomeController::class, 'storeUser']);
Route::get('/users/{id}', [HomeController::class, 'users']);
