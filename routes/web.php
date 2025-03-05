<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Página de inicio
Route::get('/', function () {
    return view('Home.HomeView');
});

// Rutas
Route::get('/Login', function () {
    return view('Login.signUp');
});

Route::get('/Noticias', function () {
    return view('Noticias.NoticiasView');
});

Route::get('/Tienda', function () {
    return view('Tienda.TiendaView');
});

Route::get('/Entrenadores', function () {
    return view('Entrenadores.EntrenadoresView');
});

Route::get('/Foro', function () {
    return view('Foro.ForoView');
});

Route::get('/Calendario', function () {
    return view('Calendario.CalendarioView');
});

Route::get('/CrearPost', function () {
    return view('Foro.CrearPost');
});





// usuarios
Route::resource('/user', UserController::class);

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);













