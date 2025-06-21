<?php

namespace App\Http\Middleware;

use Closure;

class FixCookieHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (method_exists($response, 'withCookie')) {
            $response->headers->set('Access-Control-Expose-Headers', 'Set-Cookie');
            $response->headers->set('Cache-Control', 'no-cache, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
