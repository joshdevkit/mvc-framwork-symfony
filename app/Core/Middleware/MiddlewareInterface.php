<?php

namespace App\Core\Middleware;

use Symfony\Component\HttpFoundation\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, \Closure $next);
}
