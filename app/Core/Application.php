<?php

namespace App\Core;

use eftec\bladeone\BladeOne;

class Application
{
    private static BladeOne $blade;
    private array $models = [];
    private array $views = [];
    private array $controllers = [];

    public function registerModel(string $name, $class)
    {
        $this->models[$name] = $class;
    }

    public function registerView(string $name, string $path)
    {
        $this->views[$name] = $path;
    }

    public function registerController(string $name, $class)
    {
        $this->controllers[$name] = $class;
    }

    public function getModel(string $name)
    {
        return $this->models[$name] ?? null;
    }

    public function getViewPath(string $name)
    {
        return $this->views[$name] ?? null;
    }

    public function getController(string $name)
    {
        return $this->controllers[$name] ?? null;
    }

    public function setupBlade()
    {
        $viewsPath = __DIR__ . '/../../resources/views';
        $cachePath = __DIR__ . '/../../storage/framework/cache';

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        self::$blade = new BladeOne($viewsPath, $cachePath, BladeOne::MODE_AUTO);
    }

    public static function renderView(string $view, array $data = [])
    {
        try {
            echo self::$blade->run($view, $data);
        } catch (\Exception $e) {
            echo "BladeOne Rendering Error: " . $e->getMessage();
        }
    }

    public function boot()
    {
        $this->setupBlade();
        $this->loadRoutes();
        Route::initializeDispatcher();
        Route::dispatch();
    }

    private function loadRoutes()
    {
        require_once __DIR__ . '/../../routes/web.php';
    }
}
