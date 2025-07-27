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


// RUTAS PUBLICAS

// --- AUTENTICACIÓN ---
// Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);
Route::resource('/user', UserController::class);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// --- HOME STATS ---
Route::get('/home-stats', fn() => response()->json([
    'users' => User::count(),
    'events' => Calendar::count(),
    'posts' => Post::count(),
]));

// --- CONTENIDO PÚBLICO ---
Route::get('/recent-news', fn() => News::orderBy('published_at', 'desc')->take(7)->get());
Route::get('/recent-products', [ProductController::class, 'recentProducts']);
Route::get('/popular-posts', [PostController::class, 'popularPosts']);

// --- CALENDARIO ---
Route::resource('/calendar', CalendarController::class)->only(['index', 'show']);
Route::get('/featured-events', [CalendarController::class, 'featuredEvents']);
Route::get('/scrap-calendar', [ScrapperController::class, 'sdcTicketsScrap']);
Route::post('/scrap-calendar', [ScrapCalendarController::class, 'store']);

// --- NOTICIAS ---
Route::get('/news', fn() => News::orderBy('published_at', 'desc')->get()->map(fn($n) => [
    'id' => $n->id,
    'title' => $n->title,
    'description' => $n->description,
    'image' => $n->image,
    'author' => $n->author,
    'published_at' => $n->published_at->toIso8601String(),
    'category' => $n->category,
]));

// --- POSTS, TRAINERS, PRODUCTOS ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/post', [PostController::class, 'index']);
Route::get('/post/get-reply/{commentId}', [PostController::class, 'getReply']);
Route::get('/trainer', [TrainerController::class, 'index']);
Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers']);
Route::get('/trainer/by-user/{userId}', [TrainerController::class, 'getTrainerByUserId']);

// --- MISC ---
Route::get('/prueba-publica', fn() => response()->json(['mensaje' => 'Sin autenticación']));
Route::get('/user-by-id/{id}', [UserController::class, 'getUserByID']);




// RUTAS PROTEGIDAS

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);
    Route::get('/user-stats/{userId}', [UserStatsController::class, 'getStats']);

    // --- CALENDARIO ---
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::put('/calendar/{calendar}', [CalendarController::class, 'update']);
    Route::delete('/calendar/{calendar}', [CalendarController::class, 'destroy']);

    // --- PRODUCTOS ---
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // --- CARRITO ---
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    // --- POSTS ---
    Route::post('/post', [PostController::class, 'store']);
    Route::put('/post/{id}', [PostController::class, 'update']);
    Route::delete('/post/{id}', [PostController::class, 'destroy']);
    Route::post('/toggle-like', [LikeController::class, 'toggleLike']);
    Route::post('/post/create-comment', [PostController::class, 'createComment']);
    Route::put('/post/update-comment/{commentId}', [PostController::class, 'updateComment']);
    Route::delete('/post/delete-comment/{commentId}', [PostController::class, 'destroyComment']);
    Route::post('/post/create-reply/{commentId}', [PostController::class, 'createReply']);
    Route::put('/post/update-reply/{replyId}', [PostController::class, 'updateReply']);
    Route::delete('/post/destroy-reply/{replyId}', [PostController::class, 'destroyReply']);

    // --- NOTICIAS ---
    Route::post('/news/{newsId}/toggle-save', [SavedNewsController::class, 'toggleSave']);
    Route::get('/saved-news', [SavedNewsController::class, 'index']);
    Route::put('/news/{id}', [NewsController::class, 'update']);
    Route::delete('/news/{id}', [NewsController::class, 'destroy']);

    // --- TRAINER ---
    Route::post('/solicitud-entrenador', [TrainerController::class, 'store']);
    Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus']);
    Route::get('/trainer-requests', [TrainerController::class, 'getAllTrainerRequests']);

    // --- CONFIGURACIÓN ---
    Route::post('/config-update-value', [ConfigurationController::class, 'updateValue']);
    Route::post('/change-password', [ConfigurationController::class, 'changePassword']);
    Route::resource('/config', ConfigurationController::class);

    // --- ENTRENAMIENTOS ---
    Route::resource('/training', TrainingController::class);
    Route::get('/training/{id}', [TrainingController::class, 'show']);
    Route::post('/training/check-existing', [TrainingController::class, 'checkExisting']);

    // --- CHATS ---
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);
    Route::post('/messages/send', [ChatController::class, 'sendMessage']);

    // --- PRUEBA AUTENTICADA ---
    Route::get('/test-auth', fn(Request $request) => response()->json(['user' => $request->user()]));
});



// RUTAS DE VERIFICACIÓN DE CORREO ELECTRÓNICO

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->has('user_id') ? User::find($request->input('user_id')) : $request->user();
    if (!$user) return response()->json(['message' => 'No autenticado.'], 401);
    if ($user->hasVerifiedEmail()) return response()->json(['message' => 'El correo ya está verificado.'], 200);
    $user->sendEmailVerificationNotification();
    return response()->json(['message' => '¡Correo de verificación enviado!']);
})->middleware(['throttle:6,1'])->name('api.verification.send');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($request->input('user_id'));
    if (!$user) return response()->json(['message' => 'No autenticado.'], 401);
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification())))
        return response()->json(['message' => 'Enlace inválido.'], 403);
    if ($user->hasVerifiedEmail())
        return response()->json(['message' => 'Correo ya verificado.', 'id' => $user->id], 200);

    $user->email_verified_at = now();
    $user->save();
    event(new \App\Events\EmailVerified($user->id));
    $query = http_build_query(['id' => $user->id]);
    return redirect("/email/verified-success?{$query}");
})->name('api.verification.verify');

Route::get('/email/verify', function (Request $request) {
    $user = $request->has('user_id') ? User::find($request->input('user_id')) : $request->user();
    if (!$user) return response()->json(['message' => 'No autenticado.'], 401);
    return response()->json(['message' => 'Por favor verifica tu correo.', 'user' => $user]);
})->name('api.verification.notice');
