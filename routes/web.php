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

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;

Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->noContent()->withHeaders([
        'Access-Control-Allow-Origin' => 'http://localhost:5173',
        'Access-Control-Allow-Credentials' => 'true'
    ]);
});

Route::post('login', [AuthController::class, 'login'])->middleware('cors');
Route::post('logout', [AuthController::class, 'logout']);
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;


// Rutas públicas
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

// Agrupar rutas protegidas por auth

Route::middleware(['auth'])->group(function () {
    // usuarios
    Route::resource('/user', UserController::class);
    Route::get('/user-stats/{user}', [UserController::class, 'stats']);
    Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);

    // productos
    Route::resource('/products', ProductController::class);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'destroyProduct']);

    // Posts
    Route::resource('/post', PostController::class);

    // configuracion
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

    // Noticias guardadas
    Route::post('/news/{newsId}/toggle-save', [SavedNewsController::class, 'toggleSave']);
    Route::get('/saved-news', [SavedNewsController::class, 'index']);

    // Trainer
    Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);
    Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus']);
    Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers']);
    Route::resource('/trainer', TrainerController::class);

    // Configuración
    Route::post('/config-update-value', [ConfigurationController::class, 'updateValue']);
    Route::post('/change-password', [ConfigurationController::class, 'changePassword']);
    Route::resource('config', ConfigurationController::class);
});

// Noticias guardadas
Route::middleware('auth')->group(function () {
    Route::post('/news/{newsId}/toggle-save', [SavedNewsController::class, 'toggleSave']);
    Route::get('/saved-news', [SavedNewsController::class, 'index']);
    // Training
    Route::resource('/training', TrainingController::class);
    Route::get('/training/{id}', [TrainingController::class, 'show']);

    // Chats
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{chatId}/messages', [ChatController::class, 'storeMessage']);
    Route::put('/chats/{id}/accept', [ChatController::class, 'acceptChat']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);
});

    // SCRAPPER
    Route::get('/baseball_news', [ScrapperController::class, 'baseballNews']);
    Route::get('/futbol_news', [ScrapperController::class, 'futbolNews']);
    Route::get('/basketball_news', [ScrapperController::class, 'basketballNews']);
    Route::get('/volleyball_news', [ScrapperController::class, 'volleyballNews']);
    Route::get('/swimming_news', [ScrapperController::class, 'swimmingNews']);

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

    // Operaciones CRUD en noticias (solo admin)

    Route::middleware('auth', 'can:admin')->group(function () {

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
        })->middleware('can:update,news');

        Route::delete('/news/{id}', function ($id) {
            $noticia = \App\Models\News::findOrFail($id);
            $noticia->delete();
            return response()->json(['message' => 'Noticia eliminada']);
        })->middleware('can:delete,news');
    });

    // Entrenadores
    Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);
    Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus']);
    Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers']);
    Route::get('/trainer/by-user/{userId}', [TrainerController::class, 'getTrainerByUserId']);
    Route::resource('/trainer', TrainerController::class);

    // Entrenamientos
    Route::resource('/training', TrainingController::class);
    Route::get('/training/{id}', [TrainingController::class, 'show']);
    Route::post('/training/check-existing', [TrainingController::class, 'checkExisting']);
    Route::post('/training', [TrainingController::class, 'store']);

    // Chats
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{chatId}/messages', [ChatController::class, 'storeMessage']);
    Route::put('/chats/{id}/accept', [ChatController::class, 'acceptChat']);
    Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);

    Route::post('/chats/{chat}/messages', [MessageController::class, 'store']);

    // --- RUTAS DE VERIFICACIÓN DE EMAIL (deben ir antes del catch-all) ---

    // Broadcasting
    Broadcast::routes(['middleware' => ['auth:sanctum']]);


    // API para verificar email sin sesión activa
    Route::get('/api/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
        $user = \App\Models\User::findOrFail($id);
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Enlace de verificación inválido.'], 403);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya está verificado.'], 200);
        }
        $user->markEmailAsVerified();
        return response()->json(['message' => 'Correo verificado con éxito.'], 200);
    });

    // Muestra la vista de "Verifica tu email"
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    // aqui laravel Maneja el clic en el enlace de verificación (el del correo)
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/home');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    // aqui para reenvíar el email de verificación
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');


    // --- MANSI, LA RUTA CATCH-ALL SPA AL FINAL SIEMPRE ---
    Route::get('/{any}', function () {
        return view('app');
    })->where('any', '.*');
