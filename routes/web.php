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
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
