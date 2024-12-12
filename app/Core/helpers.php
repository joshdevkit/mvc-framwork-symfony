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
        /**
         * Flash a message to the session.
         */
        public function with(string $key, $message)
        {
            $_SESSION[$key] = $message;
            return $this;
        }
    };
}

/**
 * Handle session data.
 *
 * @param array|string|null $key
 * @param mixed $default
 * @return mixed
 */
function session($key = null, $default = null)
{
    if (is_array($key)) {
        foreach ($key as $k => $value) {
            $_SESSION[$k] = $value;
        }
    } elseif (is_string($key)) {
        return $_SESSION[$key] ?? $default;
    } elseif ($key === null) {
        return $_SESSION;
    }
}

/**
 * Forget a session variable.
 *
 * @param string $key
 */
function session_forget(string $key)
{
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        error_log("Session key '$key' has been removed.");
    }
}


/**
 * Retrieve old input values from the session.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function old($key, $default = null)
{
    return $_SESSION['old_input'][$key] ?? $default;
}



/**
 * Generate a URL for an asset in the public/ directory.
 *
 * @param string $path
 * @return string
 */
function asset(string $path): string
{
    $baseUrl = $_ENV['APP_URL'];
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}


function url(string $path): string
{
    $baseUrl = $_ENV['APP_URL'];
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}


/**
 * Retrieve configuration values.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config(string $key, $default = null)
{
    static $config = [];

    if (empty($config)) {
        foreach (glob(__DIR__ . '/../../config/*.php') as $file) {
            $name = basename($file, '.php');
            $config[$name] = require $file;
        }
    }

    $keys = explode('.', $key);
    $value = $config;

    foreach ($keys as $segment) {
        if (isset($value[$segment])) {
            $value = $value[$segment];
        } else {
            return $default;
        }
    }

    return $value;
}


/**
 * Retrieve the value of an environment variable or a default value.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    static $env = [];

    if (empty($env)) {
        $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, '"');
            $env[$name] = $value;
        }
    }

    return $env[$key] ?? $default;
}
