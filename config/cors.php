<?php

return [
    'paths' => ['api/*', 'broadcasting/*'],
    'allowed_methods' => ['*'],
    // En local, sin CORS_ALLOWED_ORIGINS definido, se acepta cualquier origen
    // (comodo para probar desde varias IPs de red local). En produccion se
    // fija a una lista separada por comas, p. ej. "https://tuapp.onrender.com".
    'allowed_origins' => array_values(array_filter(
        explode(',', env('CORS_ALLOWED_ORIGINS', '*'))
    )),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true
];
