<?php

namespace App\Core;

use App\Core\Traits\Dispatcher;
use App\Core\Validations\UseErrors;
use App\Http\Kernel;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Route
{
    use UseErrors, Dispatcher;


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



    private static function invokeController($action, $vars, $customRequest = null)
    {
        list($controller, $method) = $action;
        $controller = new $controller;

        if (!method_exists($controller, $method)) {
            if (!config('app.debug')) {
                self::GenericError();
            } else {
                self::sendMethodNotFoundError($method, get_class($controller));
            }
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


    public static function sendMethodNotFoundError($method, $controller)
    {
        $instance = new self();
        $instance->returnMethodNotFound($method, $controller);
        exit();
    }
}
