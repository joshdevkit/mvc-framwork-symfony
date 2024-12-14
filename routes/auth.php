<?php

use  App\Core\Route;
use App\Http\Controllers\Auth\AuthController;


Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/signup', [AuthController::class, 'register']);
Route::get('/signin', [AuthController::class, 'login']);
Route::post('/signin', [AuthController::class, 'authenticate']);
Route::post('/signup', [AuthController::class, 'storeUser']);
