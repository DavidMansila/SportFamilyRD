<?php

use Illuminate\Support\Facades\Route;

// Ruta para mostrar la vista de aviso de verificación (solo para flujo web tradicional)
Route::get('/email/verify', function () {
    return view('app'); // Carga el SPA para cualquier /email/verify
})->name('verification.notice');

// Redirigir /email/verify/{id}/{hash} (web) a /api/email/verify/{id}/{hash} con todos los query params
Route::get('/email/verify/{id}/{hash}', function ($id, $hash) {
    $query = http_build_query(request()->query());
    $url = "/api/email/verify/$id/$hash" . ($query ? ("?" . $query) : "");
    return redirect($url);
})->name('verification.verify');

// --- RUTA CATCH-ALL PARA EL SPA ---
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
