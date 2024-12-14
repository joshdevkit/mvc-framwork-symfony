<?php

namespace App\Core;

use App\Core\Middleware\MiddlewareStack;
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
        $basePath = dirname(__DIR__, 2);
        $viewsPath = $basePath . '/resources/views';
        $layoutsPath = $viewsPath . '/layouts';
        $cachePath = $basePath . '/storage/framework/cache';

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        self::$blade = new BladeOne([$viewsPath, $layoutsPath], $cachePath, BladeOne::MODE_DEBUG);

        if (file_exists($cachePath)) {
            array_map('unlink', glob("$cachePath/*"));
        }

        self::$blade->directive('csrf', function () {
            $csrfToken = csrf_token();
            return "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . htmlspecialchars('$csrfToken') . '\">'; ?>";
        });

        self::$blade->directive('auth', function () {
            return "<?php if (isset(\$_SESSION['user_id'])): ?>";
        });

        self::$blade->directive('endauth', function () {
            return "<?php endif; ?>";
        });

        self::$blade->directive('error', function ($field) {
            return "<?php if (isset(\$_SESSION['errors']) && isset(\$_SESSION['errors'][$field])): ?>
                    <?php foreach (\$_SESSION['errors'][$field] as \$message): ?>
                        <div class='invalid-feedback mt-1'><?= \$message ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>";
        });

        self::$blade->directive('enderror', function () {
            return "";
        });
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
        //in publications of dependency change the value to 3
        $routesPath = dirname(__DIR__, 2) . '/routes/web.php';

        if (file_exists($routesPath)) {
            require_once $routesPath;
        } else {
            throw new \Exception("Routes file not found at: {$routesPath}");
        }
    }
}
