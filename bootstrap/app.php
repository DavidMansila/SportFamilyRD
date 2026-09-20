<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',

        // -------------------------------------------------------------------
        // GET /health  -> 200 {"status":"ok"}
        // -------------------------------------------------------------------
        //
        // Para el monitor externo que despierta la instancia de Render cada
        // pocos minutos. Tiene que ser lo mas barato posible: es una peticion
        // que se repite dia y noche y nadie mira.
        //
        // Va aqui, en 'then', y NO en routes/web.php a proposito: las rutas de
        // ese archivo entran en el grupo de middleware 'web', que arranca la
        // sesion, y con SESSION_DRIVER=database eso significa abrir una
        // conexion y escribir una fila en 'sessions' EN CADA PING. Definida
        // aqui no pasa por ningun grupo: sin sesion, sin cookies, sin CSRF y
        // sin tocar la base de datos. Solo la atraviesan los middleware
        // globales (CORS y cabeceras de seguridad), que no consultan nada.
        //
        // Aqui estaba antes "health: '/up'", el endpoint que trae Laravel 11.
        // Se quito porque no servia para esto por dos motivos: lo registra
        // tambien dentro del grupo 'web' (misma sesion en BD), y ademas nunca
        // llegaba a responder -el catch-all del SPA en routes/web.php se
        // registra primero y se lo comia, asi que /up devolvia el HTML de la
        // aplicacion con un 200 enganoso-.
        //
        // Para que el catch-all no haga lo mismo con /health, la ruta esta
        // excluida en su expresion regular (ver routes/web.php).
        then: function () {
            Route::get('/health', function () {
                return response()
                    ->json(['status' => 'ok'])
                    // Que ningun proxy ni CDN cachee la respuesta: un 200
                    // servido de cache diria que la instancia esta viva
                    // aunque estuviera caida.
                    ->header('Cache-Control', 'no-store, max-age=0');
            })->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Render pone la app detras de su propio proxy HTTPS -> HTTP interno.
        // Sin confiar en el (via la cabecera X-Forwarded-Proto), Laravel cree
        // que toda peticion llega por HTTP plano y genera URLs/assets/cookies
        // de sesion como inseguros. '*' es lo recomendado para PaaS donde el
        // unico proxy que le habla al contenedor es el de la propia plataforma.
        $middleware->trustProxies(at: '*');


        // Autorizacion de canales privados de broadcasting (chat).
        // Solo auth:sanctum: identifica al usuario por el Bearer token y deja
        // que Broadcast::channel() de routes/channels.php decida si puede
        // entrar al canal.
        //
        // Antes aqui tambien iba BroadcastAuth, que hacia Auth::login() sobre
        // un guard sin estado y reventaba con "RequestGuard::login does not
        // exist" -> /api/broadcasting/auth devolvia 500 SIEMPRE, Echo nunca
        // lograba suscribirse y el chat en tiempo real no funcionaba nunca.
        // Era redundante: auth:sanctum ya deja al usuario en el request.
        $middleware->appendToGroup('broadcast', [
            'auth:sanctum',
        ]);

        $middleware->prepend([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Cabeceras de seguridad en TODAS las respuestas (SPA y API). La app no
        // emitia ninguna: ni CSP, ni HSTS, ni X-Frame-Options, ni nosniff.
        // La CSP sale en modo Report-Only hasta que CSP_ENFORCE=true.
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->web(append: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Limite de peticiones para TODO /api. En Laravel 11 el grupo 'api' no
        // lo trae puesto: hay que pedirlo explicitamente. Sin esta linea, las
        // 60 rutas con auth:sanctum no tenian ningun limite de tasa (los
        // throttle:60,1 sueltos cubrian solo algunas rutas publicas).
        // El limitador 'api' se define en App\Providers\RouteServiceProvider.
        $middleware->throttleApi();

        // 'token.auth' (App\Http\Middleware\VerifyToken) se elimino: leia el
        // Bearer con base64_decode("id|expiracion") y confiaba en el id que
        // venia dentro, sin firma ni consulta a la tabla de tokens. Cualquiera
        // que mandara base64("1|9999999999") quedaba autenticado como el
        // usuario 1. No estaba enganchado a ninguna ruta, pero bastaba una
        // palabra en routes/api.php para activarlo. La autenticacion de esta
        // API es auth:sanctum y no hay motivo para tener una segunda via.
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'broadcast.auth' => \App\Http\Middleware\BroadcastAuth::class,

            // Acciones reservadas a administracion. Va detras de 'auth:sanctum'
            // y sustituye a la comprobacion de user_type que estaba copiada en
            // cada metodo: asi la regla se lee en routes/api.php, junto a la
            // ruta, y no se puede olvidar dentro del controlador -que es como
            // se abrieron dos agujeros en este proyecto-.
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,

            // Responde 202 y ejecuta el comando despues de enviar la respuesta.
            // Lo usan las rutas de cron de routes/api.php para no agotar los
            // 30s que espera cron-job.org. Uso: 'cron.background:news:import'.
            'cron.background' => \App\Http\Middleware\RunCommandAfterResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Firma invalida en un enlace de verificacion de correo.
        //
        // La causa casi siempre es la misma y el 403 pelado no lo dice: APP_URL
        // no coincide con el host por el que se sirve la app. La firma se
        // calcula sobre la URL COMPLETA (host incluido), asi que si el correo se
        // genera con APP_URL=http://localhost:8000 y la persona entra por otro
        // host o puerto, la firma no cuadra y el enlace legitimo se rechaza.
        //
        // Antes esto no se notaba porque la ruta no validaba la firma -que es
        // justamente lo que la hacia vulnerable-. Ahora que si la valida,
        // APP_URL tiene que estar bien o NADIE puede verificar su correo.
        $exceptions->render(function (\Illuminate\Routing\Exceptions\InvalidSignatureException $e, $request) {
            if (! $request->is('api/email/verify/*')) {
                return null;
            }

            \Illuminate\Support\Facades\Log::warning('Enlace de verificacion con firma invalida', [
                'url_recibida' => $request->fullUrl(),
                'app_url_configurado' => config('app.url'),
                'pista' => 'Si ambos difieren en host o puerto, el problema es APP_URL, no el enlace.',
            ]);

            return response()->json([
                'message' => 'Este enlace de verificación no es válido o ya expiró. '
                    . 'Solicita uno nuevo desde la aplicación.',
            ], 403);
        });

        // Todo lo que cuelga de /api debe responder JSON siempre, incluidos los
        // errores. Sin esto, una peticion sin cabecera Accept que falle la
        // autenticacion intenta redirigir a una ruta 'login' que no existe en
        // esta API y termina en un 500 confuso en vez de un 401 limpio.
        $exceptions->shouldRenderJsonWhen(
            fn($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
