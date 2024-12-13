<?php

namespace App\Core\Middleware;

class ClearSessionErrors
{
    public function handle($request, $next)
    {
        $response = $next($request);

        if (isset($_SESSION['errors_displayed']) && $_SESSION['errors_displayed']) {
            session_forget('errors');
            session_forget('errors_displayed');
        }

        if (isset($_SESSION['old_input_retained']) && $_SESSION['old_input_retained']) {
            session_forget('old_input');
            session_forget('old_input_retained');
        }

        if (isset($_SESSION['errors'])) {
            $_SESSION['errors_displayed'] = true;
        }

        if (isset($_SESSION['old_input'])) {
            $_SESSION['old_input_retained'] = true;
        }

        return $response;
    }
}
