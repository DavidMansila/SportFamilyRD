<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        $parts = explode('|', base64_decode($token));

        if (count($parts) !== 2 || $parts[1] < time()) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        // Obtener el usuario y adjuntarlo al request
        $user = \App\Models\User::find($parts[0]);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 401);
        }

        // Adjuntar el usuario a la solicitud
        $request->merge(['user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
