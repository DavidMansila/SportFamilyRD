<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustHostS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function hosts()
    {
        return [
            '^localhost$',
            '^127\.0\.0\.1$',
            '^localhost:8000$',
            '^localhost:5173$',
            '^127\.0\.0\.1:8000$',
            '^127\.0\.0\.1:5173$',
        ];
    }

    /**
     * Get a pattern that matches all subdomains of the application URL.
     *
     * @return string
     */
}
