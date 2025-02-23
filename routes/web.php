<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Login.signUp');
});

Route::get('/welcome', function () {
    return view('welcome');
});

//usuarios
Route::resource( 'user', UserController::class);

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);