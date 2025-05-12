<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PostController;
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


// Rutas para las vistas
Route::get('/Directorio', function () {
    return view('Directorio.DirectorioView');
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



Route::get('/SolicitudesUsuarios', function () {
    return view('Solicitudes.SolicitudesUsuarios');
});

Route::get('/SolicitudesEntrenadores', function () {
    return view('Solicitudes.SolicitudesEntrenadores');
});


Route::get('/Foro', function () {
    return view('Foro.ForoView');
});


Route::get('/Ajustes', function () {
    return view('Ajustes.AjustesView');
});


Route::get('/Perfil', function () {
    return view('Perfil.PerfilView');
});


//Rutas para funciones en el back

// usuarios
Route::resource('/user', UserController::class);

//Noticias
Route::resource('/news', NewsController::class);

//productos
Route::resource('/products', ProductController::class);

// Posts
Route::resource('/post', PostController::class);

// Comentarios
Route::post('/post/create-comment', [PostController::class, 'createComment']);
Route::put('/post/update-comment/{commentId}', [PostController::class, 'updateComment']);
Route::delete('/post/delete-comment/{commentId}', [PostController::class, 'destroyComment']);


// Respuestas
Route::get('/post/get-reply/{commentId}', [PostController::class, 'getReply']);
Route::post('/post/create-reply/{commentId}', [PostController::class, 'createReply']);
Route::post('/post/update-reply/{replyId}', [PostController::class, 'updateReply']);
Route::post('/post/destroy-reply/{replyId}', [PostController::class, 'destroyReply']);


// Route::post('/post/{post}/likes_quantity', [PostController::class, 'updateLikes']);

// Rutas de autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);



//scraper
Route::get('/baseball_news', [ScrapperController::class, 'baseballNews']);
Route::get('/futbol_news', [ScrapperController::class, 'futbolNews']);
Route::get('/basketball_news', [ScrapperController::class, 'basketballNews']);
Route::get('/volleyball_news', [ScrapperController::class, 'volleyballNews']);
Route::get('/swimming_news', [ScrapperController::class, 'swimmingNews']);
