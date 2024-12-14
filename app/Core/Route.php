<?php

namespace App\Core;

use App\Http\Kernel;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Route
{
    private static $dispatcher;
    public static $routes = [];
    protected static $middleware = [];

    public static function get($uri, $action)
    {
        self::addRoute('GET', $uri, $action);
    }

    public static function post($uri, $action)
    {
        self::addRoute('POST', $uri, $action);
    }

    public static function delete($uri, $action)
    {
        self::addRoute('DELETE', $uri, $action);
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
    private static function addRoute($method, $uri, $action)
    {
        self::$routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'action'     => $action,
            'middleware' => self::$middleware,
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
                self::send404();
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                self::send405();
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

                // Apply middlewares
                foreach ($middlewares as $middleware) {
                    error_log("Executing middleware: $middleware");
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


    private static function send404()
    {
        http_response_code(404);
        echo "404 Not Found";
    }

    private static function send405()
    {
        http_response_code(405);
        echo "405 Method Not Allowed";
    }
}
