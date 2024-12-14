<?php

namespace App\Http;

use App\Core\Request;

class Kernel
{
    protected $routeMiddleware = [
        'auth' => 'App\Http\Middleware\AuthMiddleware',
        'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
    ];

    public function handle(Request $request, \Closure $next)
    {
        return $next($request);
    }

    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }
}
