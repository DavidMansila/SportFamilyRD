<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->bearerToken()) {
            // Token-based authentication
            if (!Auth::guard('sanctum')->check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
        } else {
            // Session-based authentication
            if (!Auth::guard('web')->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        return $next($request);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
