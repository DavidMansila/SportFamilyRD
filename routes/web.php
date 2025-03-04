<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Página de inicio
Route::get('/home', function () {
    return view('Home.HomeView');
});

// Ruta de login
Route::get('/', function () {
    return view('Login.signUp');
});

// usuarios
Route::resource('user', UserController::class);

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);




