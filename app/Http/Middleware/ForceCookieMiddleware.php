<?php

namespace App\Http\Middleware;

use Closure;

class ForceCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        config([
            'session.secure' => false,
            'session.http_only' => true,
            'session.same_site' => 'lax',
            'session.domain' => 'localhost' 
        ]);

        return $next($request);
    }
}
