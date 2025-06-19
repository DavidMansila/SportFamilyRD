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
        // Compartir usuario autenticado con todas las vistas
        if (Auth::check()) {
            View::share('authUser', Auth::user());
        } else {
            View::share('authUser', null);
        }

        if ($request->wantsJson()) {
            $request->attributes->set('authUser', Auth::user());
        }

        return $next($request);
    }
}
