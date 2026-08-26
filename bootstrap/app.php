<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
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

        $middleware->web(append: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'token.auth' => \App\Http\Middleware\VerifyToken::class,
            'broadcast.auth' => \App\Http\Middleware\BroadcastAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Todo lo que cuelga de /api debe responder JSON siempre, incluidos los
        // errores. Sin esto, una peticion sin cabecera Accept que falle la
        // autenticacion intenta redirigir a una ruta 'login' que no existe en
        // esta API y termina en un 500 confuso en vez de un 401 limpio.
        $exceptions->shouldRenderJsonWhen(
            fn($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
