<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// --- RUTAS DE VERIFICACIÓN DE EMAIL ---
Route::get('/api/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
    $user = \App\Models\User::findOrFail($id);
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Enlace de verificación inválido.'], 403);
    }
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El correo ya está verificado.'], 200);
    }
    $user->markEmailAsVerified();
    return response()->json(['message' => 'Correo verificado con éxito.'], 200);
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['throttle:6,1'])->name('verification.send');

// --- RUTA CATCH-ALL PARA EL SPA ---
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
