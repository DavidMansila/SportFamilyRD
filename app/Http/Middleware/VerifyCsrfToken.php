<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '*',
        'broadcasting/auth',
        'api/*',
        'sanctum/csrf-cookie',
        'chats*'
    ];

    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
