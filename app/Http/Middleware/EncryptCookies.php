<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;

class EncryptCookies extends BaseEncryptCookies
{
    protected $except = [
        'XSRF-TOKEN',
        'laravel_session'
    ];
}
