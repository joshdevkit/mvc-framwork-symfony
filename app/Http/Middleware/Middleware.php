<?php

namespace App\Http\Middleware;

use App\Core\Request;

interface Middleware
{
    public function handle(Request $request, \Closure $next);
}
