<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TrainerController;
use App\Models\Configuration;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Route;



//Rutas para funciones en el back

// usuarios
Route::resource('/user', UserController::class);
Route::get('/user-stats/{user}', [UserController::class, 'stats']);
Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);

//Noticias
Route::resource('/news', NewsController::class);

//productos
Route::resource('/products', ProductController::class);
Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
Route::delete('/products/{id}', [ProductController::class, 'destroyProduct']);

// Posts
Route::resource('/post', PostController::class);

//configuracion
Route::post('/config-update-value', [ConfigurationController::class, 'updateValue']);

Route::post('/change-password', [ConfigurationController::class, 'changePassword']);
Route::resource('config', ConfigurationController::class);

// Comentarios
Route::post('/post/create-comment', [PostController::class, 'createComment']);
Route::put('/post/update-comment/{commentId}', [PostController::class, 'updateComment']);
Route::delete('/post/delete-comment/{commentId}', [PostController::class, 'destroyComment']);

// Respuestas
Route::get('/post/get-reply/{commentId}', [PostController::class, 'getReply']);
Route::post('/post/create-reply/{commentId}', [PostController::class, 'createReply']);
Route::put('/post/update-reply/{replyId}', [PostController::class, 'updateReply']);
Route::delete('/post/destroy-reply/{replyId}', [PostController::class, 'destroyReply']);


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


// Trainer
Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);




// Ruta catch-all para SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
