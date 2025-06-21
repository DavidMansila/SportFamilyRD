<?php

return [

    'stateful' => [
        'localhost',
        '127.0.0.1',
        'localhost:8000',
        '127.0.0.1:8000',
        'localhost:5173',
        '127.0.0.1:5173'
    ],
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'authenticate_session' => null,
    ],

];
