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

/**
 * Generate a URL for an asset in the public/ directory.
 *
 * @param string $path
 * @return string
 */
function asset(string $path): string
{
    $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
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
        // Load all config files
        foreach (glob(__DIR__ . '/../../config/*.php') as $file) {
            $name = basename($file, '.php');
            $config[$name] = require $file;
        }
    }

    // Parse dot notation (e.g., 'app.name')
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
        // Parse the .env file
        $lines = file(__DIR__ . '/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, '"'); // Remove optional quotes
            $env[$name] = $value;
        }
    }

    return $env[$key] ?? $default;
}
