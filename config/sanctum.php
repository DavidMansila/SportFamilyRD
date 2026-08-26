<?php

use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\EncryptCookies;

return [
    
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    // NO agregar 'sanctum' a esta lista. Sanctum recorre estos guards para
    // resolver al usuario; si se incluye a si mismo, se llama en bucle
    // (sanctum -> sanctum -> ...) hasta desbordar la pila y matar el proceso
    // de PHP (en Windows: status 3221225725 / 0xC0000409). Era la causa de
    // que TODA ruta con auth:sanctum tumbara el servidor.
    // 'web' es el valor por defecto de Laravel y el correcto.
    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        // 'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];