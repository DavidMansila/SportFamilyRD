<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Nunca redirige (esta es una API pura, sin ruta 'login'): ante un
     * token invalido o ausente en una ruta auth:sanctum, siempre responde
     * 401 en JSON en vez de intentar una redireccion.
     *
     * Antes esta clase no extendia el middleware real de Laravel y su
     * handle() simplemente dejaba pasar la peticion sin verificar nada, asi
     * que auth:sanctum no aplicaba ninguna autenticacion en ninguna ruta de
     * la app (request()->user() siempre devolvia null).
     */
    protected function redirectTo($request): ?string
    {
        return null;
    }
}
