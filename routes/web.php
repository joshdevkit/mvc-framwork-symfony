<?php

use App\Core\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/users/{id}', [HomeController::class, 'users']);
