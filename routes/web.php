<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SavedNewsController;
use App\Models\Configuration;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Route;

use App\Models\News;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;


//Rutas para funciones en el back

// usuarios
Route::resource('/user', UserController::class);
Route::get('/user-stats/{user}', [UserController::class, 'stats']);
Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);

// //Noticias
// Route::resource('/news', NewsController::class);

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


// SCRAPPER
// Route::get('/baseball_news', [ScrapperController::class, 'baseballNews']);
// Route::get('/futbol_news', [ScrapperController::class, 'futbolNews']);
// Route::get('/basketball_news', [ScrapperController::class, 'basketballNews']);
// Route::get('/volleyball_news', [ScrapperController::class, 'volleyballNews']);
// Route::get('/swimming_news', [ScrapperController::class, 'swimmingNews']);


// NOTICIAS
Route::get('/news', function () {
    $news = News::orderBy('published_at', 'desc')
        ->get()
        ->map(function ($item) {
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
    $noticia = News::findOrFail($id);
    $noticia->delete();

    return response()->json(['message' => 'Noticia eliminada']);
});


// NOTICIAS GUARDADAS
Route::post('/news/{newsId}/toggle-save', [SavedNewsController::class, 'toggleSave']);
Route::get('/saved-news', [SavedNewsController::class, 'index']);


// Trainer
Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);
Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus']);
Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers']);
Route::resource('/trainer', TrainerController::class);

//training
Route::resource('/training', TrainingController::class);
Route::get('/training/{id}', [TrainingController::class, 'show']);


//Chats

Route::get('/chats', [ChatController::class, 'index']);
Route::post('/chats', [ChatController::class, 'store']);
Route::get('/chats/{id}', [ChatController::class, 'show']);
Route::post('/chats/{chatId}/messages', [ChatController::class, 'storeMessage']);
Route::put('/chats/{id}/accept', [ChatController::class, 'acceptChat']);
Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);

Route::post('/messages/send', [ChatController::class, 'sendMessage']);


Broadcast::routes(['middleware' => ['auth:sanctum']]);



// Ruta catch-all para SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
