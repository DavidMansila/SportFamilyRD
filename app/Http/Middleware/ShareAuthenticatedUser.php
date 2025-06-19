<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ShareAuthenticatedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Verificar token Sanctum
        if ($request->bearerToken()) {
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable;
            if ($user) {
                Auth::setUser($user);
                View::share('authUser', $user);
                $request->attributes->set('authUser', $user);
                return $next($request);
            }
        }

        // Compartir usuario autenticado con todas las vistas
        if (Auth::check()) {
            View::share('authUser', Auth::user());
            $request->attributes->set('authUser', Auth::user());
        } else {
            View::share('authUser', null);
            $request->attributes->set('authUser', null);
        }

        return $next($request);
    }
}
