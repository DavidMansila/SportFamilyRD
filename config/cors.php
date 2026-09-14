<?php

return [
    'paths' => ['api/*', 'broadcasting/*'],
    'allowed_methods' => ['*'],
    // Lista explicita de origenes, separada por comas. El valor por defecto es
    // VACIO (= solo mismo origen), no '*'.
    //
    // '*' con 'supports_credentials' => true es una combinacion que la propia
    // especificacion CORS prohibe, y ademas dejaba la API abierta a cualquier
    // origen si alguien olvidaba definir la variable en Render. Un valor por
    // defecto permisivo convierte un olvido en un agujero; uno restrictivo lo
    // convierte en un error visible que se arregla en un minuto.
    //
    // Laravel sirve el SPA y la API desde el mismo host, asi que en uso normal
    // el navegador no hace ninguna peticion cross-origin: la lista vacia es el
    // valor correcto salvo que exista un frontend en otro dominio.
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true
];
