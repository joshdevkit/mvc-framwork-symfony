<?php

namespace App\Core;

use App\Core\Middleware\MiddlewareStack;
use App\Core\Traits\BladeDirectivesTrait;
use eftec\bladeone\BladeOne;

class Application
{
    use BladeDirectivesTrait;

    private static BladeOne $blade;

    public function setupBlade()
    {
        $viewsPath = RESOURCE_VIEW_PATH;
        $layoutsPath = $viewsPath . RESOURCE_VIEW_LAYOUTS;
        $cachePath = STORAGE_RESOURCES;

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        // Initialize and assign the Blade instance to the static property
        self::$blade = new BladeOne([$viewsPath, $layoutsPath], $cachePath, BladeOne::MODE_DEBUG);

        // Register custom Blade directives
        /**
         * @var BladeOne $blade The BladeOne instance where the directives will be registered.
         * @return void
         */
        self::registerBladeDirectives(self::$blade);
    }

    public static function renderView(string $view, array $data = [])
    {
        try {
            // we can add a control flow to check if blade initialze first, nor throw exception
            /**
             * if (!isset(self::$blade)) {
             * throw new \Exception("BladeOne has not been initialized. Call setupBlade() first.");
             * }
             */
            // but in this case we don't do that since its already define on the method registerBladeDirectives
            // Since self::$blade is a static property, you don't need to reinitialize it in every method.
            echo self::$blade->run($view, $data);
        } catch (\Exception $e) {
            echo "BladeOne Rendering Error: " . $e->getMessage();
        }
    }

    /**
     * Bootstraps the application and handles the request lifecycle.
     *
     * This method performs the following tasks:
     * 1. Loads all routes.
     * 2. Initializes the session if not already started.
     * 3. Sets up BladeOne for view rendering.
     * 4. Processes the middleware stack.
     * 5. Initializes and dispatches the route to handle the incoming request.
     *
     * @return void
     */
    public function run()
    {
        $this->loadRoutes();

        if (session_status() == PHP_SESSION_NONE) {

            session_save_path(config('session.path'));
            session_start();
        }

        $this->setupBlade();


        $middlewareStack = new MiddlewareStack();
        $middlewareStack->handle(Request::createFromGlobals());

        Route::initializeDispatcher();
        Route::dispatch();
    }

    private function loadRoutes()
    {
        $routesPath = ROUTE_PATH;

        if (file_exists($routesPath)) {
            require_once $routesPath;
        } else {
            throw new \Exception("Routes file not found at: {$routesPath}");
        }
    }
}
