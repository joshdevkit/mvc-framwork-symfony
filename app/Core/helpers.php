<?php

use App\Core\Application;
use App\Core\Redirector;
use App\Core\Response;

/**
 * Render a view with BladeOne.
 *
 * @param string $view
 * @param array $data
 */
function view(string $view, array $data = [])
{
    Application::renderView($view, $data);
}



/**
 * Redirect to a URL.
 *
 * @param string $url
 * @param integer $status
 * @param array $headers
 * @return RedirectResponse
 */
function response()
{
    return new class {
        public function json(array $data, int $status = 200, array $headers = [], int $options = 0)
        {
            $response = Response::json($data, $status, $headers, $options);
            $response->send();
            exit();
        }
    };
}
function redirect()
{
    return new class {
        public function to(string $url, int $status = 302, array $headers = [])
        {
            $response = Redirector::to($url, $status, $headers);
            $response->send();
            exit();
        }
        public function back(array $data = [])
        {
            $response = Redirector::back($data);
            $response->send();
            exit();
        }
    };
}
