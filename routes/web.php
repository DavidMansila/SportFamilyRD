<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReplyController;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Route;

// Página de inicio
Route::get('/', function () {
    return view('Home.HomeView');
});

// Rutas para las vistas
Route::get('/Login', function () {
    return view('Login.signUp');
});

Route::get('/Noticias', function () {
    return view('Noticias.NoticiasView');
});

Route::get('/Calendario', function () {
    return view('Calendario.CalendarioView');
});

Route::get('/Tienda', function () {
    return view('Tienda.TiendaView');
});

Route::get('/Entrenadores', function () {
    return view('Entrenadores.EntrenadoresView');
});

Route::get('/Solicitud', function () {
    return view('Entrenadores.SolicitudView');
});

Route::get('/Foro', function () {
    return view('Foro.ForoView');
});

Route::get('/Publicacion', function () {
    return view('Foro.ForoPublicaciones');
});

Route::get('/CrearPost', function () {
    return view('Foro.CrearPost');
});

Route::get('/Ajustes', function () {
    return view('Ajustes.AjustesView');
});


//Rutas para funciones en el back

// usuarios
Route::resource('/user', UserController::class);

//Noticias
Route::resource('/news', NewsController::class);

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


//scraper
Route::get('/baseball_news', [ScrapperController::class, 'baseballNews']);



Route::apiResource('posts', PostController::class);
Route::apiResource('comments', CommentController::class);
Route::apiResource('replies', ReplyController::class);





