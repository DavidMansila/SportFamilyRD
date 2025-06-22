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

        // Validación simple (en producción usaría un sistema real)
        $parts = explode('|', base64_decode($token));

        if (count($parts) !== 2 || $parts[1] < time()) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        return $next($request);
    }
}
