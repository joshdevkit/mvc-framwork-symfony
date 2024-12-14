<?php

namespace App\Http\Middleware;

use App\Core\Request;

class RedirectIfAuthenticated implements Middleware
{
    public function handle(Request $request, \Closure $next)
    {
        /**
         * RedirectIfAuthenticated
         *
         * If the user is authenticated:
         * - Redirect them to the homepage (`/`).
         *
         * If not authenticated:
         * - Allow access to the next middleware or controller.
         */
        if (auth()->user()) {
            // Redirect authenticated users to the homepage
            return redirect()->back();
        }

        // Continue for unauthenticated users
        return $next($request);
    }
}
