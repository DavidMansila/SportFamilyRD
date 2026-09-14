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

    // Minutos de vida de un token de acceso. 'null' (el valor anterior)
    // significaba que NO CADUCAN NUNCA: un token filtrado -por un volcado de
    // debug, por un dispositivo compartido, por XSS- daba acceso permanente a
    // la cuenta, sin rotacion ni forma de expirarlo salvo un logout explicito.
    //
    // 7 dias es el equilibrio para este sitio: el token vive en sessionStorage
    // y ya se pierde al cerrar la pestaña, asi que subir de aqui no aporta
    // comodidad real y solo alarga la ventana de un token robado.
    //
    // Ojo: Sanctum solo comprueba la caducidad, no borra las filas vencidas.
    // Conviene programar 'sanctum:prune-expired' (ver routes/console.php).
    'expiration' => 60 * 24 * 7,

    'middleware' => [
        // 'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];