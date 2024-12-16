<?php

use App\Core\Application;
use App\Core\Auth;
use App\Core\Exceptions\MissingRouteParamsException;
use App\Core\Redirector;
use App\Core\Response;
use App\Core\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

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
        //for calling the route name
        public function route(string $name, array $params = [])
        {
            $url = route($name, $params);
            $this->to($url);
        }
        //for simplicity
        public function to_route(string $name, array $params = [])
        {
            $url = route($name, $params);
            $this->to($url);
        }

        public function back(array $data = [])
        {
            $response = Redirector::back($data);
            $response->send();
            exit();
        }

        public function with($message)
        {
            session()->flash('message', $message);
            $this->back();
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
    if (!isset($_SESSION)) {
        session_start();
    }
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

function flash($key, $value)
{
    $_SESSION[$key] = $value;
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


// /**
//  * Display validation errors for a given field.
//  *
//  * @param string $field
//  */
// function errors($field)
// {
//     if (session('errors') && isset(session('errors')[$field])) {
//         foreach (session('errors')[$field] as $error) {
//             echo "<div class='invalid-feedback mt-1'>{$error}</div>";
//         }
//     }
// }


/**
 * Generate a URL for an asset in the public/ directory.
 *
 * @param string $path
 * @return string
 */
function asset(string $path): string
{
    $baseUrl = config('app.url');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}


function url(string $path): string
{
    $baseUrl = config('app.url');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}


function route(string $name, array $params = []): string
{
    $route = Route::getRouteByName($name);
    if (!$route) {
        throw new Exception("The route {$name} was not found.");
    }

    $url = $route['uri'];
    foreach ($params as $key => $value) {
        $url = str_replace("{{$key}}", $value, $url);
    }

    return url($url);
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

    $keys = explode('.', $key); // Parse dot notation keys
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


/**
 * Generate and retrieve the CSRF token. 
 * 
 * @param string $tokenId 
 * @return string
 */
function csrf_token($tokenId = '_token'): string
{
    if (isset($_SESSION['csrf_tokens'][$tokenId])) {
        return $_SESSION['csrf_tokens'][$tokenId];
    }

    static $csrfTokenManager;
    if (!$csrfTokenManager) {
        $csrfTokenManager = new CsrfTokenManager();
    }

    $token = $csrfTokenManager->getToken($tokenId);

    if ($token && $token->getValue()) {
        $_SESSION['csrf_tokens'][$tokenId] = substr($token->getValue(), 0, 40);
        return $_SESSION['csrf_tokens'][$tokenId];
    }

    $tokenValue = bin2hex(random_bytes(20));
    $_SESSION['csrf_tokens'][$tokenId] = $tokenValue;

    return $tokenValue;
}


/**
 * Store an uploaded file in the specified location.
 *
 * @param UploadedFile $file
 * @param string $path
 * @param string $name
 * @return string|null
 */
function storeAs($file, string $path, string $name): ?string
{
    $directory = public_path($path);
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $filename = $name . '.' . $file->getClientOriginalExtension();
    $filePath = $directory . '/' . $filename;

    if ($file->move($directory, $filename)) {
        return asset("{$path}/{$filename}");
    }

    return null;
}

/**
 * Return the full public path.
 */
function public_path(string $path): string
{
    return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($path, '/');
}



function auth(): Auth
{
    return Auth::instance();
}
