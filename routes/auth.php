<?php

use App\Core\Route;
use App\Http\Controllers\Auth\AuthController;


Route::post('/signin', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/signup', [AuthController::class, 'register']);
