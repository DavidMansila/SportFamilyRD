<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SavedNewsController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserStatsController;
use Illuminate\Support\Facades\Route;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;


Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);

// RUTAS PÚBLICAS
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Noticias públicas
Route::get('/news', function () {
    $news = \App\Models\News::orderBy('published_at', 'desc')->get()->map(function ($item) {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'image' => $item->image,
            'author' => $item->author,
            'published_at' => $item->published_at->toIso8601String(),
            'category' => $item->category,
        ];
    });
    return response()->json($news);
});

// Scrapper calendar
Route::get('/scrap-calendar', [ScrapperController::class, 'sdcTicketsScrap']);

// RUTAS PROTEGIDAS POR TOKEN
Route::middleware('auth:sanctum')->group(function () {

    // Usuarios
    Route::resource('/user', UserController::class);
    Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);
    Route::get('/user-stats/{userId}', [UserStatsController::class, 'getStats']);

    // Productos
    Route::resource('/products', ProductController::class);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'destroyProduct']);

    // Carrito
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);

    // Posts
    Route::resource('/post', PostController::class);
    Route::post('/toggle-like', [LikeController::class, 'toggleLike']);

    // Funcionalidades completas de posts
    Route::post('/post/create-comment', [PostController::class, 'createComment']);
    Route::put('/post/update-comment/{commentId}', [PostController::class, 'updateComment']);
    Route::delete('/post/delete-comment/{commentId}', [PostController::class, 'destroyComment']);
    Route::get('/post/get-reply/{commentId}', [PostController::class, 'getReply']);
    Route::post('/post/create-reply/{commentId}', [PostController::class, 'createReply']);
    Route::put('/post/update-reply/{replyId}', [PostController::class, 'updateReply']);
    Route::delete('/post/destroy-reply/{replyId}', [PostController::class, 'destroyReply']);

    // Noticias guardadas
    Route::post('/news/{newsId}/toggle-save', [SavedNewsController::class, 'toggleSave']);
    Route::get('/saved-news', [SavedNewsController::class, 'index']);

    // Trainer
    Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);
    Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus']);
    Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers']);
    Route::get('/trainer/by-user/{userId}', [TrainerController::class, 'getTrainerByUserId']);
    Route::resource('/trainer', TrainerController::class);

    // Configuración
    Route::post('/config-update-value', [ConfigurationController::class, 'updateValue']);
    Route::post('/change-password', [ConfigurationController::class, 'changePassword']);
    Route::resource('config', ConfigurationController::class);

    // Entrenamientos
    Route::resource('/training', TrainingController::class);
    Route::get('/training/{id}', [TrainingController::class, 'show']);
    Route::post('/training/check-existing', [TrainingController::class, 'checkExisting']);
    Route::post('/training', [TrainingController::class, 'store']);

    // Chats
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
    Route::post('/chats/{chat}/messages', [MessageController::class, 'store']);

    // CRUD de noticias (solo admin)
    Route::middleware('can:admin')->group(function () {
        Route::put('/news/{id}', function (Request $request, $id) {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'author' => 'required|string|max:100',
                'published_at' => 'required|date',
                'categoria' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $noticia = News::findOrFail($id);
            $noticia->update([
                'title' => $request->title,
                'description' => $request->description,
                'author' => $request->author,
                'published_at' => $request->published_at,
                'category' => $request->categoria
            ]);

            return response()->json($noticia);
        });

        Route::delete('/news/{id}', function ($id) {
            $noticia = \App\Models\News::findOrFail($id);
            $noticia->delete();
            return response()->json(['message' => 'Noticia eliminada']);
        });
    });
});
