<?php

namespace App\Core\Middleware;

class ClearSessionErrors
{
    public function handle($request, $next)
    {
        $response = $next($request);

        // if (isset($_SESSION['errors_displayed']) && $_SESSION['errors_displayed']) {
        //     session_forget('errors');
        //     session_forget('errors_displayed');
        // }
        if (check('errors_displayed')  && check('errors_displayed')) {
            session_forget('errors');
            session_forget('errors_displayed');
        }

        if (check('message_set') && check('message_set')) {
            session_forget('message');
            session_forget('message_set');
        }

        if (check('old_input_retained') && check('old_input_retained')) {
            session_forget('old_input');
            session_forget('old_input_retained');
        }

        if (check('message_displayed') && check('message_displayed')) {
            session_forget('message');
            session_forget('message_displayed');
        }

        if (check('errors')) {
            $_SESSION['errors_displayed'] = true;
        }

        if (check('old_input')) {
            $_SESSION['old_input_retained'] = true;
        }

        if (check('message')) {
            $_SESSION['message_displayed'] = true;
        }

        return $response;
    }
}
