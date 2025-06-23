<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BroadcastAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $header = $request->header('Authorization');
            Log::info('BroadcastAuth header', ['header' => $header]);
            if ($header && preg_match('/Bearer\\s(.+)/', $header, $matches)) {
                $token = PersonalAccessToken::findToken($matches[1]);
                Log::info('BroadcastAuth token', ['token' => $token]);
                if ($token) {
                    Auth::login($token->tokenable);
                    Log::info('BroadcastAuth user logged in', ['user' => Auth::user()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('BroadcastAuth error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Broadcast auth error', 'error' => $e->getMessage()], 500);
        }
        return $next($request);
    }
}
