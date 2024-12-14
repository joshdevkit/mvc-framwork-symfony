<?php

namespace App\Http\Middleware;

use App\Core\Request;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, \Closure $next)
    {
        /**
         * AuthMiddleware
         * 
         * This middleware checks if a user is authenticated before allowing access to the requested route.
         * 
         * 1. The `auth()->user()` function attempts to retrieve the currently authenticated user.
         * 2. If no user is found (i.e., the user is not logged in), it redirects the request to the '/signin' page.
         * 3. If a user is authenticated, it calls the `$next` closure to pass the request to the next middleware or the controller.
         */
        if (!auth()->user()) {
            return redirect()->back();
        }

        return $next($request);
    }
}
