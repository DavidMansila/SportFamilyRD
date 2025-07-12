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
use App\Models\Calendar;
use Illuminate\Support\Facades\Route;
use App\Models\News;
use App\Models\User;
use App\Models\Post;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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
            'events' => Calendar::count(),
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



Route::resource('/user', UserController::class);


// RUTAS PROTEGIDAS POR TOKEN
Route::middleware('auth:sanctum')->group(function () {


    // Usuarios
    Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);
    Route::get('/user-stats/{userId}', [UserStatsController::class, 'getStats']);


    // Calendario
    Route::resource('/calendar', CalendarController::class);
    Route::get('/scrap-calendar', [ScrapperController::class, 'sdcTicketsScrap']);
    Route::post('/scrap-calendar', [ScrapCalendarController::class, 'store']);
    Route::get('/', [CalendarController::class, 'index']);
    Route::post('/', [CalendarController::class, 'store']);
    Route::put('/{calendar}', [CalendarController::class, 'update']);
    Route::delete('/{calendar}', [CalendarController::class, 'destroy']);

    // Productos
    Route::resource('/products', ProductController::class);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'destroyProduct']);

    // Carrito
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);
    Route::delete('cart/clear', [CartController::class, 'clearCart']);

    // Posts
    Route::resource('/post', PostController::class);
    Route::post('/post', [PostController::class, 'store']);
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
    Route::get('/trainer-requests', [TrainerController::class, 'getAllTrainerRequests']);


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
    Route::post('/chats/{chat}/messages', [ChatController::class, 'store']);


    Route::put('/news/{id}', [NewsController::class, 'update']);

    Route::delete('/news/{id}', [NewsController::class, 'destroy']);
});

// --- RUTAS DE VERIFICACIÓN DE EMAIL (API) ---
// Reenviar correo de verificación
Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();
    if ($request->has('user_id')) {
        $user = User::find($request->input('user_id'));
    }
    if (!$user) {
        return response()->json(['message' => 'No autenticado.'], 401);
    }
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.'], 200);
    }
    $user->sendEmailVerificationNotification();
    return response()->json(['message' => '¡Correo de verificación enviado!'], 200);
})->middleware(['throttle:6,1'])->name('api.verification.send');

// Verificar correo electrónico
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($request->input('user_id'));
    if (!$user) {
        return response()->json(['message' => 'No autenticado.'], 401);
    }
    // Validar hash
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Enlace de verificación inválido.'], 403);
    }
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.', 'id' => $user->id], 200);
    }
    // Forzar actualización manual del campo email_verified_at
    $user->email_verified_at = now();
    $user->save();
    // Emitir evento de broadcast para Pusher
    event(new \App\Events\EmailVerified($user->id));
    $query = http_build_query(['id' => $user->id]);
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['message' => 'Correo verificado con exito.', 'id' => $user->id], 200);
    }
    return redirect("/email/verified-success?{$query}");
})->name('api.verification.verify');

// Aviso de verificación
Route::get('/email/verify', function (Request $request) {
    $user = $request->user();
    if ($request->has('user_id')) {
        $user = User::find($request->input('user_id'));
    }
    if (!$user) {
        return response()->json([
            'message' => 'No autenticado.',
            'user' => $user,
        ], 401);
    }
    return response()->json([
        'message' => 'Por favor verifica tu correo.',
        'user' => $user,
    ], 200);
})->name('api.verification.notice');


Route::middleware('auth:sanctum')->get('/test-auth', function (Request $request) {
    return response()->json(['user' => $request->user()]);
});

Broadcast::routes([
    'middleware' => ['api', 'auth:sanctum', 'broadcast.auth']
]);

// ENDPOINT para eventos destacados en home
Route::get('/featured-events', [CalendarController::class, 'featuredEvents']);

// Obtener usuario por ID (para refresco tras verificación de email)
Route::get('/user-by-id/{id}', [UserController::class, 'getUserByID']);
