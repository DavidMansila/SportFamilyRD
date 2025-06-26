<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
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
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapCalendarController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserStatsController;
use Illuminate\Support\Facades\Route;
use App\Models\News;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;


Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);

// RUTAS PÚBLICAS
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/recent-news', function () {
    $news = \App\Models\News::orderBy('published_at', 'desc')->take(7)->get();
    return response()->json($news);
});

Route::get('/recent-products', [ProductController::class, 'recentProducts']);

Route::get('/popular-posts', [PostController::class, 'popularPosts']);


Route::get('/home-stats', function () {
    try {
        return response()->json([
            'users' => User::count(),
            // 'events' => Event::count()
            'posts' => Post::count(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error obteniendo estadísticas',
            'error' => $e->getMessage()
        ], 500);
    }
});



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
Route::post('/scrap-calendar', [ScrapCalendarController::class, 'store']);




// RUTAS PROTEGIDAS POR TOKEN
Route::middleware('auth:sanctum')->group(function () {
    //calendario
    Route::resource('/calendar', CalendarController::class);
    
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
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
    Route::post('/chats/{chat}/messages', [MessageController::class, 'store']);


    Route::put('/news/{id}', [NewsController::class, 'update']);

    Route::delete('/news/{id}', [NewsController::class, 'destroy']);
    
});


Broadcast::routes([
    'middleware' => ['api', 'auth:sanctum', 'broadcast.auth']
]);
