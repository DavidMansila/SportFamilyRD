<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Remover encabezados inseguros
if (!headers_sent() && function_exists('header_remove')) {
    header_remove('X-Powered-By');
    header_remove('Server');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ShareAuthenticatedUser::class,
        ]);

        // Add Sanctum middleware for API stateful requests
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Register custom middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'cors' => \App\Http\Middleware\EnsureCors::class,
            'fix.cookies' => \App\Http\Middleware\FixCookieHeaders::class,
            'force.cookies' => \App\Http\Middleware\ForceCookieMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Apply critical middleware globally
        $middleware->append([
            \App\Http\Middleware\ForceCookieMiddleware::class,
            \App\Http\Middleware\FixCookieHeaders::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return response()->json(['message' => 'CSRF token expired'], 419);
            }
            return null;
        });
    })->create();
