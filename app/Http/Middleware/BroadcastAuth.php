<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Respaldo para la autorizacion de canales privados de broadcasting.
 *
 * Normalmente auth:sanctum ya deja al usuario resuelto en el request antes de
 * llegar aqui; este middleware solo actua si por alguna razon no fue asi.
 *
 * Antes hacia Auth::login($token->tokenable), pero el guard activo en una API
 * es stateless (RequestGuard) y no tiene login(): eso lanzaba
 * "Method Illuminate\Auth\RequestGuard::login does not exist", el middleware
 * devolvia 500 y /api/broadcasting/auth fallaba SIEMPRE. Resultado: Echo nunca
 * conseguia suscribirse al canal privado del chat y el tiempo real no
 * funcionaba en ningun momento. setUser() es lo correcto aqui: asigna el
 * usuario a la peticion sin tocar sesion.
 */
class BroadcastAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            return $next($request);
        }

        $bearer = $request->bearerToken();

        if ($bearer) {
            $token = PersonalAccessToken::findToken($bearer);

            if ($token && $token->tokenable) {
                Auth::setUser($token->tokenable);
            }
        }

        return $next($request);
    }
}
