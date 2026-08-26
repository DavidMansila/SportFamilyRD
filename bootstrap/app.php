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
        // Agrega esto para broadcasting
        $middleware->appendToGroup('broadcast', [
            \App\Http\Middleware\Authenticate::class,
            \App\Http\Middleware\BroadcastAuth::class,
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
