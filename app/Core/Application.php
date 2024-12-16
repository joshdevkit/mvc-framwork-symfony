<?php

namespace App\Core;

use App\Core\Middleware\MiddlewareStack;
use eftec\bladeone\BladeOne;

class Application
{
    private static BladeOne $blade;

    public function setupBlade()
    {
        $viewsPath =  RESOURCE_VIEW_PATH;
        $layoutsPath = $viewsPath . RESOURCE_VIEW_LAYOUTS;
        $cachePath = STORAGE_RESOURCES;

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
        $routesPath = ROUTE_PATH;

        if (file_exists($routesPath)) {
            require_once $routesPath;
        } else {
            throw new \Exception("Routes file not found at: {$routesPath}");
        }
    }
}
