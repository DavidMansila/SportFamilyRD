<?php

use Illuminate\Support\Facades\Route;

// Ruta para mostrar la vista de aviso de verificación (solo para flujo web tradicional)
Route::get('/email/verify', function () {
    return view('app'); // Carga el SPA para cualquier /email/verify
})->name('verification.notice');

// Servir la SPA en /email/verify/{id}/{hash} para que Vue maneje la verificación
Route::get('/email/verify/{id}/{hash}', function () {
    return view('app');
})->name('verification.verify');

// --- ANTERIOR: Redirigir /email/verify/{id}/{hash} a la API (ya no se usa, solo referencia) ---
// Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
//     $query = http_build_query(request()->query());
//     $url = "/api/email/verify/$id/$hash" . ($query ? ("?" . $query) : "");
//     return redirect($url);
// })->name('verification.verify');

// Servir la SPA en /email/verified-success para que Vue maneje la pantalla de éxito
Route::get('/email/verified-success', function () {
    return view('app');
});

// --- RUTA CATCH-ALL PARA EL SPA ---
//
// Excluye las rutas de ARCHIVOS. Antes capturaba absolutamente todo, asi que
// una imagen que no existiera en disco no daba 404: caia aqui y devolvia el
// HTML del SPA con estado 200. El navegador recibe una pagina donde espera un
// JPEG, no se queja en consola, y el fallo pasa inadvertido.
//
// Asi se escondio el fondo roto de la Tienda (pedia /public/imagenes/... en
// lugar de /imagenes/...): en produccion no se veia la imagen y no habia ningun
// error que lo delatara. Con esta exclusion, un asset que falte responde 404 y
// se ve al instante en la pestaña de red.
//
// Los archivos que SI existen los sirve Apache antes de llegar a Laravel (ver
// las RewriteCond de public/.htaccess), asi que esto solo afecta a los que no.
// 'health' tambien queda fuera: es el ping de disponibilidad (definido en
// bootstrap/app.php, fuera del grupo 'web' para no abrir sesion en cada
// llamada). Sin esta exclusion lo atraparia esta ruta -que se registra antes-
// y el monitor recibiria el HTML del SPA en vez del JSON, con 200 igualmente.
// Es justo lo que le pasaba al /up que trae Laravel.
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!storage/|imagenes/|build/|defaults/|public/|health$).*');
