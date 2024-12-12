<?php

namespace App\Core;

use Symfony\Component\HttpFoundation\Request;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Route
{
    private static $dispatcher;
    public static $routes = [];

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

    private static function addRoute($method, $uri, $action)
    {
        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action
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

    public static function dispatch()
    {
        $request = Request::createFromGlobals();
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
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $vars);
                } else {
                    self::invokeController($handler, $vars);
                }
                break;
        }
    }

    private static function invokeController($action, $vars)
    {
        list($controller, $method) = $action;
        $controller = new $controller;

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in controller " . get_class($controller));
        }

        call_user_func_array([$controller, $method], $vars);
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
