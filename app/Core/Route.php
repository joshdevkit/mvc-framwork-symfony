<?php

namespace App\Core;

use App\Core\Validations\UseErrors;
use App\Http\Kernel;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Route
{
    use UseErrors;
    private static $dispatcher;
    public static $routes = [];
    protected static $middleware = [];

    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
        return new static;
    }

    public static function post($uri, $action)
    {
        self::addRoute('POST', $uri, $action);
        return new static;
    }

    public static function delete($uri, $action)
    {
        self::addRoute('DELETE', $uri, $action);
        return new static;
    }

    /**
     * Assign middlewares
     */
    public static function middleware(array $middleware)
    {
        self::$middleware = $middleware;
        return new static();  // Enable chaining
    }

    /**
     * Group middlewares and register routes
     */
    public static function group(\Closure $callback)
    {
        $callback();
        self::$middleware = [];  // Clear middlewares after grouping
    }

    /**
     * Register the routes with their methods and middlewares
     */
    private static function addRoute($method, $uri, $action, $name = null)
    {
        self::$routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'action'     => $action,
            'middleware' => self::$middleware,
            'name'       => $name,
        ];
    }

    public static function initializeDispatcher()
    {
        self::$dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach (self::$routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    public static function name($name)
    {
        $lastKey = array_key_last(self::$routes);
        if ($lastKey !== null) {
            self::$routes[$lastKey]['name'] = $name;
        }
        return new static;
    }

    public static function getRouteByName($name)
    {
        foreach (self::$routes as $route) {
            if (isset($route['name']) && $route['name'] === $name) {
                return $route;
            }
        }
        return null; // Return null if not found
    }

    /**
     * Dispatch requests and handle middleware
     */
    public static function dispatch()
    {
        $request = SymfonyRequest::createFromGlobals();
        $customRequest = Request::createFromSymfonyRequest($request);

        $requestUri = $request->getPathInfo();
        $requestMethod = $request->getMethod();

        $routeInfo = self::$dispatcher->dispatch($requestMethod, $requestUri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                if (config('app.debug')) {
                    self::send404();
                } else {
                    self::GenericError();
                }
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1][0];
                $httpMethod = $_SERVER['REQUEST_METHOD'];
                $controllName = self::$routes[3]['action'][0];
                $uRi = self::$routes[3]['uri'];
                if (config('app.debug')) {
                    self::GenericError();
                } else {
                    self::send405($httpMethod, $allowedMethods, $controllName, $uRi);
                }
                break;

            case \FastRoute\Dispatcher::FOUND:

                $handler   = $routeInfo[1];
                $vars      = $routeInfo[2];

                $route = array_filter(self::$routes, fn($r) => $r['action'] === $handler);
                $route = array_shift($route);

                $middlewares = $route['middleware'] ?? [];
                $kernel = new Kernel;

                $next = function ($request) use ($handler, $vars, $customRequest) {
                    self::invokeController($handler, $vars, $customRequest);
                };

                foreach ($middlewares as $middleware) {
                    $next = function ($request) use ($middleware, $next) {
                        return self::invokeMiddleware($middleware, $request, $next);
                    };
                }

                $kernel->handle($customRequest, $next);
                break;
        }
    }

    private static function invokeController($action, $vars, $customRequest = null)
    {
        list($controller, $method) = $action;
        $controller = new $controller;

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in controller " . get_class($controller));
        }

        $arguments = [...array_values($vars), $customRequest];

        call_user_func_array([$controller, $method], $arguments);
    }

    private static function invokeMiddleware($middleware, $request, $next)
    {
        $kernel = new Kernel();
        $routeMiddleware = $kernel->getRouteMiddleware();

        if (isset($routeMiddleware[$middleware])) {
            $middlewareClass = $routeMiddleware[$middleware];

            if (class_exists($middlewareClass)) {
                return (new $middlewareClass)->handle($request, $next);
            }
        }

        throw new \Exception("Middleware `$middleware` not found.");
    }


    public static function send404()
    {
        $instance = new self();
        $instance->return404Error();
        exit();
    }


    public static function send405($httpMethod, $allowedMethods, $controllName, $uRi)
    {
        $instance = new self();
        $instance->return405Error($httpMethod, $allowedMethods, $controllName, $uRi); // Pass details to the trait method
        exit();
    }


    public static function GenericError()
    {
        $instance = new self();
        $instance->returnGenericError();
        exit();
    }
}
