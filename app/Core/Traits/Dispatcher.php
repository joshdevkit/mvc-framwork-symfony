<?php

namespace App\Core\Traits;

use Symfony\Component\HttpFoundation\Request;
use App\Core\Request as CoreRequest;
use App\Http\Kernel;

trait Dispatcher
{

    private static $dispatcher;
    public static $routes = [];

    public static function dispatch()
    {
        $request = Request::createFromGlobals();
        $customRequest = CoreRequest::createFromSymfonyRequest($request);

        $requestUri = $request->getPathInfo();
        $requestMethod = $request->getMethod();

        $routeInfo = self::$dispatcher->dispatch($requestMethod, $requestUri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                if (!config('app.debug')) {
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
                if (!config('app.debug')) {
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
}
