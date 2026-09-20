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
// ELIMINADO: MessageController. Su unico metodo, store(), escribia en el
// chat_id que le pasaran SIN comprobar que quien llama participe en esa
// conversacion -a diferencia de ChatController::storeMessage, que si lo
// comprueba- y ademas fijaba el sender_type por el rol global de la cuenta.
// Nunca estuvo enrutado, pero era una copia aparentemente equivalente del
// endpoint real: reactivarla habria reabierto un IDOR de escritura.
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScrapCalendarController;
use App\Http\Controllers\ScrapperController;
use App\Http\Controllers\SportController;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Support\CacheDeContenido;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


// RUTAS PUBLICAS

// --- AUTENTICACIÓN ---
// Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);
// Solo store (registro) es publico; index/show no los usa el frontend y
// exponian nombre/email/telefono/fecha de nacimiento de TODOS los usuarios sin
// autenticacion. update/destroy requieren estar autenticado y ser dueño de la
// cuenta (ver auth:sanctum mas abajo): antes este resource completo era
// publico, lo que permitia editar o borrar CUALQUIER cuenta (incluyendo
// password y user_type) sin iniciar sesion.
// throttle:registro (5/hora por IP): el registro es publico y cada alta exitosa
// dispara un correo de verificacion. Sin limite se podia crear cuentas en masa
// y, con ellas, mandar correo a direcciones de terceros a voluntad.
Route::resource('/user', UserController::class)->only(['store'])
    ->middleware('throttle:registro');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout']);


// --- HOME STATS ---
// Son tres COUNT(*) contra Supabase (base de datos remota): tardaban ~1,4 s en
// devolver 35 bytes, y se pagaban en CADA carga del Home. Con 60 s de cache el
// Home deja de esperar por ellos.
//
// El realtime pide ?fresh=1 para saltarse el cache a proposito: cuando llega el
// aviso de que cambio el numero de usuarios, eventos o posts hay que leer el
// valor de verdad, que es justamente para lo que existe esa suscripcion. Sin
// esta salida, el contador se quedaria congelado hasta un minuto despues de que
// alguien publica algo, y la suscripcion no serviria de nada.
Route::get('/home-stats', function (Request $request) {
    // Solo cuentan las cuentas VERIFICADAS. Una cuenta sin verificar es una
    // direccion de correo que nadie ha confirmado: puede ser un registro a
    // medias, una prueba o un alta automatizada, y no representa a una persona
    // de la comunidad. Es el numero que se enseña en la portada, asi que tiene
    // que significar algo.
    //
    // El MISMO filtro esta en broadcast_user_count_change() (ver la migracion
    // del trigger). Si los dos dejan de coincidir, el contador cambia de valor
    // al refrescarse en vivo y vuelve a cambiar al recargar la pagina.
    $calcular = fn() => [
        'users' => User::whereNotNull('email_verified_at')->count(),
        'events' => Calendar::count(),
        'posts' => Post::count(),
    ];

    if ($request->boolean('fresh')) {
        $stats = $calcular();
        Cache::put('home-stats', $stats, 60);

        return response()->json($stats);
    }

    return response()->json(Cache::remember('home-stats', 60, $calcular));
})->middleware('throttle:60,1');

// --- CONTENIDO PÚBLICO ---
// Cacheado: lo que se ahorra no es tanto la consulta como ABRIR la conexion a
// Supabase (~500 ms de TLS + autenticacion en cada peticion). Una respuesta
// servida desde el cache no toca la base de datos, asi que no paga nada de eso.
// El cache se limpia solo cuando cambia la tabla (ver el booted() de News).
Route::get('/recent-news', fn() => Cache::remember(
    'recent-news',
    CacheDeContenido::MINUTOS_CONTENIDO,
    fn() => News::orderBy('published_at', 'desc')->take(7)->get()
));
Route::get('/recent-products', [ProductController::class, 'recentProducts']);
Route::get('/popular-posts', [PostController::class, 'popularPosts']);

// --- CALENDARIO ---
Route::resource('/calendar', CalendarController::class)->only(['index', 'show'])->middleware('throttle:60,1');
Route::get('/featured-events', [CalendarController::class, 'featuredEvents'])->middleware('throttle:60,1');
// Scraping en vivo (golpea un sitio externo): limite bajo para que no se
// pueda usar esta ruta publica para tumbar el servicio ni al sitio scrapeado.
Route::get('/scrap-calendar', [ScrapperController::class, 'sdcTicketsScrap'])->middleware('throttle:5,1');
// POST /scrap-calendar se movio al grupo auth:sanctum (solo admin): escribia en
// la tabla del calendario sin autenticacion ni validacion de ningun tipo.

// --- NOTICIAS ---
// LISTADO de noticias: va SIN el texto completo del articulo.
//
// Medido con 183 noticias: la respuesta pesaba 420 KB y el 87% de eso era el
// campo 'description' (1.985 caracteres de media). El listado solo pinta un
// extracto de 120 caracteres por tarjeta, asi que se estaban descargando ~366
// KB de texto que nadie llegaba a leer.
//
// La primera idea fue paginar en el servidor, pero el front filtra por
// deporte, busca por texto y ordena poniendo delante las noticias guardadas de
// cada usuario: paginar en el servidor obligaria a mover toda esa logica y
// romperia tres cosas para arreglar una. Recortar el campo pesado consigue el
// mismo ahorro sin tocar nada de eso.
//
// El texto completo se pide aparte al abrir la noticia (ver GET /news/{id}).
Route::get('/news', fn() => Cache::remember(
    'news-index',
    CacheDeContenido::MINUTOS_CONTENIDO,
    fn() => News::orderBy('published_at', 'desc')->get()->map(fn($n) => [
        'id' => $n->id,
        'title' => $n->title,
        // 300 caracteres: la tarjeta corta en 120, y el margen sobrante evita
        // que un extracto quede raro si algun dia se alarga ese recorte.
        'description' => Str::limit((string) $n->description, 300),
        'image' => $n->image,
        'author' => $n->author,
        'published_at' => $n->published_at->toIso8601String(),
        'category' => $n->category,
    ])
))->middleware('throttle:60,1');

// UNA noticia con su texto completo. Lo pide el pop-out al abrirse, porque el
// listado ya no lo trae. Es una sola fila, asi que sale barato.
Route::get('/news/{id}', fn($id) => Cache::remember(
    'news-item-' . (int) $id,
    CacheDeContenido::MINUTOS_CONTENIDO,
    function () use ($id) {
        $n = News::findOrFail($id);

        return [
            'id' => $n->id,
            'title' => $n->title,
            'description' => $n->description,
            'image' => $n->image,
            'author' => $n->author,
            'published_at' => $n->published_at->toIso8601String(),
            'category' => $n->category,
        ];
    }
))->whereNumber('id')->middleware('throttle:60,1');

// --- POSTS, TRAINERS, PRODUCTOS ---
Route::get('/products', [ProductController::class, 'index'])->middleware('throttle:60,1');
Route::get('/post', [PostController::class, 'index'])->middleware('throttle:60,1');
Route::get('/post/get-reply/{commentId}', [PostController::class, 'getReply'])->middleware('throttle:60,1');
Route::get('/trainer/approved', [TrainerController::class, 'getAprovedTrainers'])->middleware('throttle:60,1');
Route::get('/trainer/by-user/{userId}', [TrainerController::class, 'getTrainerByUserId'])->middleware('throttle:60,1');

// --- DIRECTORIO DE DEPORTES ---
Route::get('/sports', [SportController::class, 'index'])->middleware('throttle:60,1');

// --- MISC ---
Route::get('/user-by-id/{id}', [UserController::class, 'getUserByID'])
    ->whereNumber('id')
    ->middleware('throttle:60,1');

// --- URLS PARA EL CRON EXTERNO (cron-job.org) ---
//
// Dos jobs, uno por scraper. Cada URL ejecuta UN comando y nada mas:
//
//   GET /api/internal/cron/news      -> php artisan news:import
//   GET /api/internal/cron/calendar  -> php artisan calendar:import
//
// Las dos responden 202 de inmediato y hacen el trabajo DESPUES de enviar la
// respuesta (middleware cron.background, en terminate()). Antes corrian el
// comando dentro de la peticion y news:import tardaba ~28s, pegado a los 30s
// a los que cron-job.org corta y marca el job como fallido aunque el servidor
// lo terminara bien.
//
// Siguen siendo dos rutas y no una que dispare ambos: cada scraper conserva su
// propio horario, y como produccion corre con el servidor embebido de PHP
// -una peticion a la vez, ver Dockerfile- mientras uno scrapea el sitio no
// responde. Mejor dos ratos cortos en horas distintas que uno largo.
//
// El token se manda en la cabecera X-Cron-Token (en cron-job.org: pestana
// Advanced -> Headers). Tambien se acepta ?token= por comodidad al probar,
// pero en la URL queda escrito en los logs de acceso de Render, en cualquier
// proxy intermedio y en el historial de ejecuciones del propio cron.
//
// config('app.cron_secret') y no env('CRON_SECRET'): despues de un
// "php artisan config:cache" env() devuelve null fuera de config/*.php, y
// comparar contra null dejaria estas rutas abiertas a cualquiera.
$cronAutorizado = function (Request $request): bool {
    $secret = config('app.cron_secret');

    // Sin CRON_SECRET en .env las rutas quedan desactivadas solas.
    if (! $secret) {
        return false;
    }

    $recibido = (string) $request->header('X-Cron-Token', '');
    if ($recibido === '') {
        $recibido = (string) $request->query('token', '');
    }

    return hash_equals($secret, $recibido);
};

// 202 Accepted y no 200: cuando esta respuesta sale, el comando todavia no ha
// empezado. El middleware lo arranca justo despues, y solo si el estado es 202
// -asi un 403 por token invalido no dispara nada-.
//
// El Content-Length explicito no es decorativo. En produccion el servidor es
// el embebido de PHP ("artisan serve"), donde no existe
// fastcgi_finish_request(): Laravel vacia el buffer hacia el cliente, pero la
// conexion sigue abierta hasta que el proceso termina. Un cliente que no sabe
// cuantos bytes esperar se queda escuchando hasta el cierre, o sea los ~28s
// enteros, que es justo lo que estamos evitando. Con la cabecera puesta lee
// esos bytes, da la respuesta por completa y cuelga.
$cronAceptar = function (string $comando) {
    $cuerpo = json_encode([
        'message' => "{$comando} aceptado; se ejecuta en segundo plano",
    ]);

    return response($cuerpo, 202, [
        'Content-Type' => 'application/json',
        'Content-Length' => (string) strlen($cuerpo),
    ]);
};

Route::get('/internal/cron/news', function (Request $request) use ($cronAutorizado, $cronAceptar) {
    if (! $cronAutorizado($request)) {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    return $cronAceptar('news:import');
})->middleware(['throttle:10,1', 'cron.background:news:import']);

Route::get('/internal/cron/calendar', function (Request $request) use ($cronAutorizado, $cronAceptar) {
    if (! $cronAutorizado($request)) {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    return $cronAceptar('calendar:import');
})->middleware(['throttle:10,1', 'cron.background:calendar:import']);

// --- CRON EXTERNO (Render free no trae cron propio) ---
// Un servicio externo (p. ej. cron-job.org) llama esta ruta cada minuto con
// ?token=CRON_SECRET para disparar el scheduler de Laravel (news:import,
// calendar:import, training:expire). Sin el token correcto, 403. Si
// CRON_SECRET no esta configurado en .env, la ruta se desactiva sola.
Route::get('/internal/schedule-run', function (Request $request) use ($cronAutorizado) {
    // Se reutiliza $cronAutorizado, igual que las otras dos rutas de cron. Esta
    // se habia quedado con su propia comprobacion, que solo miraba
    // ?token= y no admitia la cabecera X-Cron-Token: el secreto no tenia mas
    // remedio que ir en la URL, donde queda escrito en los logs de acceso de
    // Render, en cualquier proxy por el que pase y en el historial de
    // ejecuciones del servicio de cron. Ahora acepta la cabecera, que es la
    // forma de llamarla sin dejar el secreto por escrito; el ?token= se
    // mantiene para no romper el cron que ya esta configurado.
    if (! $cronAutorizado($request)) {
        return response()->json(['message' => 'No autorizado'], 403);
    }
    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    return response()->json(['message' => 'Scheduler ejecutado']);
})->middleware('throttle:10,1');

// ELIMINADA: GET /internal/artisan.
//
// Ejecutaba comandos artisan de una lista blanca (migrate --force incluido) y
// devolvia su salida, con el secreto viajando EN LA URL: queda escrito en los
// logs de acceso de Render, en cualquier proxy intermedio, en el historial del
// navegador y se filtra por la cabecera Referer a cualquier recurso externo
// que cargue la pagina. Era una consola de administracion remota sin
// autenticacion de usuario.
//
// Lo que resolvia (no hay shell en el plan gratuito de Render) ahora lo hace
// el arranque del contenedor: el Dockerfile corre "migrate --force" antes de
// levantar el servidor, que es donde corresponde.




// RUTAS PROTEGIDAS

Route::middleware('auth:sanctum')->group(function () {

    Route::put('/user/{user}', [UserController::class, 'update']);
    Route::delete('/user/{user}', [UserController::class, 'destroy']);
    Route::post('/user/{user}/image', [UserController::class, 'updateAvatar']);
    Route::get('/user-stats/{userId}', [UserStatsController::class, 'getStats']);

    // --- CALENDARIO (solo admin, ver check dentro del controller) ---
    Route::post('/scrap-calendar', [ScrapCalendarController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::put('/calendar/{calendar}', [CalendarController::class, 'update']);
    Route::delete('/calendar/{calendar}', [CalendarController::class, 'destroy']);

    // --- PRODUCTOS (solo admin, ver check dentro del controller) ---
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

    // --- TRAINER (index/updateStatus/trainer-requests solo admin, ver checks
    // dentro del controller) ---
    Route::get('/trainer', [TrainerController::class, 'index']);
    // Manda correo al admin en cada peticion -> throttle:correo (5/hora).
    Route::post('/solicitud-entrenador', [TrainerController::class, 'store'])
        ->middleware('throttle:correo');
    Route::put('/trainer/{id}', [TrainerController::class, 'update']);
    // Aprobar/rechazar manda correo al solicitante -> throttle:correo.
    Route::put('/update-status/{id}', [TrainerController::class, 'updateStatus'])
        ->middleware('throttle:correo');
    Route::get('/trainer-requests', [TrainerController::class, 'getAllTrainerRequests']);

    // --- CONFIGURACIÓN ---
    Route::post('/config-update-value', [ConfigurationController::class, 'updateValue']);
    Route::post('/change-password', [ConfigurationController::class, 'changePassword']);
    Route::resource('/config', ConfigurationController::class);

    // --- ENTRENAMIENTOS ---
    // check-existing va ANTES del resource: si no, "/training/{training}" del
    // resource captura "check-existing" como si fuera un id.
    Route::post('/training/check-existing', [TrainingController::class, 'checkExisting']);
    // El resource ya publica GET /training/{training} -> show(). La linea
    // "Route::get('/training/{id}', ...)" que habia aqui era la misma ruta
    // declarada dos veces: ganaba la primera y la otra quedaba inalcanzable.
    // store() manda correo al entrenador y update() al solicitante -> throttle:correo.
    Route::resource('/training', TrainingController::class)
        ->only(['index', 'show', 'destroy']);
    Route::post('/training', [TrainingController::class, 'store'])
        ->middleware('throttle:correo');
    Route::match(['put', 'patch'], '/training/{training}', [TrainingController::class, 'update'])
        ->middleware('throttle:correo');

    // --- CHATS ---
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chats/{id}/read', [ChatController::class, 'markAsRead']);
    // ELIMINADA: POST /messages/send -> ChatController@sendMessage. Ese metodo
    // no existe en el controlador, asi que la ruta solo podia devolver un 500.
    // El envio de mensajes va por POST /chats/{id}/messages.

    // ELIMINADA: GET /test-auth. Devolvia el objeto User completo del
    // solicitante; servia para confirmar de un vistazo si un token robado
    // seguia siendo valido.
});



// RUTAS DE VERIFICACIÓN DE CORREO ELECTRÓNICO

// Reenviar el correo de verificacion. Solo para el usuario autenticado: antes
// aceptaba un 'user_id' del cliente sin comprobar nada, asi que servia para
// mandarle correos de verificacion a la cuenta de cualquier otra persona.
//
// El envio va dentro de un try/catch a proposito. Antes esta ruta respondia
// siempre "¡Correo de verificación enviado!" con un 200, aunque el envio no
// hubiera ocurrido, y el frontend pintaba el mensaje verde: el usuario se
// quedaba esperando un correo que nunca iba a llegar, sin rastro del fallo en
// ningun sitio. Ahora, si el transporte falla, se registra el error (visible en
// los logs de Render) y se devuelve 503 para que el frontend avise de verdad.
//
// Ademas se comprueba el mailer configurado: con MAIL_MAILER=log -que es el
// valor por defecto de config/mail.php cuando la variable no esta puesta en el
// entorno- Laravel NO manda nada, solo escribe el correo en el log, y lo hace
// sin lanzar ninguna excepcion. Ese silencio es exactamente lo que hacia que
// todo pareciera correcto desde fuera.
Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.'], 200);
    }

    $mailer = config('mail.default');

    if (in_array($mailer, ['log', 'array'], true) && app()->environment('production')) {
        Log::error('MAIL_MAILER no esta configurado en produccion: el correo de verificacion no se envia', [
            'mailer' => $mailer,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'El servicio de correo no está configurado. Avisa al administrador.',
        ], 503);
    }

    try {
        $user->sendEmailVerificationNotification();
    } catch (\Throwable $e) {
        Log::error('Fallo al reenviar el correo de verificacion', [
            'user_id' => $user->id,
            'mailer' => $mailer,
            'error' => $e->getMessage(),
        ]);

        $respuesta = ['message' => 'No se pudo enviar el correo ahora mismo. Inténtalo de nuevo en unos minutos.'];

        // A un administrador se le devuelve ademas el motivo que dio el
        // proveedor. Es quien puede arreglarlo, y sin esto el dato solo existe
        // en los logs del servidor: en Render, si LOG_CHANNEL no es 'stderr',
        // eso es un fichero dentro del contenedor que nadie puede abrir.
        if ($user->user_type === 'admin') {
            $respuesta['detalle'] = $e->getMessage();
        }

        return response()->json($respuesta, 503);
    }

    return response()->json(['message' => '¡Correo de verificación enviado!']);
})->middleware(['auth:sanctum', 'throttle:correo'])->name('api.verification.send');

// Confirmar la verificacion desde el enlace del correo.
//
// El middleware 'signed' es lo que hace que este endpoint sea seguro: comprueba
// la firma HMAC y la caducidad del enlace que genero App\Notifications\VerifyEmail.
// Sin el, bastaba con conocer el correo de alguien para calcular su sha1 y
// verificarle la cuenta:
//
//   GET /api/email/verify/1/<sha1(correo)>?user_id=<id victima>
//
// Dos cosas mas cambian respecto a la version anterior:
//   - el usuario se resuelve por el {id} de la RUTA (que va dentro de la firma),
//     nunca por un ?user_id= que manda el cliente;
//   - el hash se compara con hash_equals contra el sha1 del correo, que ya no
//     es el unico control porque la firma lo respalda.
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($id);
    if (!$user) {
        return response()->json(['message' => 'Enlace inválido.'], 403);
    }
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Enlace inválido.'], 403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->email_verified_at = now();
        $user->save();
        event(new \App\Events\EmailVerified($user->id));
    }

    return redirect('/email/verified-success?' . http_build_query(['id' => $user->id]));
})->whereNumber('id')
  ->middleware(['signed', 'throttle:10,1'])
  ->name('api.verification.verify');

// Aviso de "verifica tu correo" para el usuario autenticado. Antes aceptaba un
// 'user_id' del cliente y devolvia el registro COMPLETO de cualquier usuario
// (correo, telefono, fecha de nacimiento, bio) sin autenticacion.
Route::get('/email/verify', function (Request $request) {
    return response()->json([
        'message' => 'Por favor verifica tu correo.',
        'user' => $request->user()->only(['id', 'name', 'email', 'email_verified_at']),
    ]);
})->middleware(['auth:sanctum', 'throttle:60,1'])->name('api.verification.notice');
