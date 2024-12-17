<?php

namespace App\Core\Traits;

use eftec\bladeone\BladeOne;

trait BladeDirectivesTrait
{
    private static BladeOne $blade;

    public static function registerBladeDirectives($blade)
    {
        // CSRF Directive
        $blade->directive('csrf', function () {
            $csrfToken = csrf_token();
            return "<?php echo '<input type=\"hidden\" name=\"_token\" value=\"' . htmlspecialchars('$csrfToken') . '\">'; ?>";
        });

        // Authenticated User Directive
        $blade->directive('auth', function () {
            return "<?php if (isset(\$_SESSION['user_id'])): ?>";
        });

        $blade->directive('endauth', function () {
            return "<?php endif; ?>";
        });

        // Error Handling Directive
        $blade->directive('error', function ($field) {
            return sprintf(
                "<?php if (isset(\$_SESSION['errors'][%s])): foreach (\$_SESSION['errors'][%s] as \$message): ?>",
                $field,
                $field
            );
        });

        $blade->directive('enderror', function () {
            return "<?php endforeach; endif; ?>";
        });
    }
}
