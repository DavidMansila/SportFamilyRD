<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ShareAuthenticatedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Agregar propiedades necesarias
            $user->image = $user->image
                ? asset('storage/users/' . $user->id . '/' . $user->image)
                : asset('storage/users/Perfil-Icon.png');

            View::share('authUser', $user);
        }

        return $next($request);
    }
}
