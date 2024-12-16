<?php

namespace App\Core\Cli;

class CliBuilder
{
    public static function handle(array $args)
    {
        if (count($args) === 0) {
            self::help();
            return;
        }

        $command = $args[0];

        switch ($command) {
            case 'create:model':
                if (isset($args[1])) {
                    self::createModel($args[1]);
                } else {
                    echo "Model name is required. Usage: php create:model ModelName\n";
                }
                break;

            case 'cache:clear':
                self::clearCache();
                break;

            case 'migrate':
                self::runMigrations();
                break;

            case 'start':
                $host = $args[1] ?? 'localhost';
                $port = $args[2] ?? 8000;
                self::serve($host, $port);
                break;

            default:
                self::help();
                break;
        }
    }

    private static function serve($host, $port)
    {
        // Ensure the public directory exists
        $basePath = dirname(__DIR__, 3); // Root directory of the application
        $publicPath = "{$basePath}/public";

        if (!file_exists($publicPath)) {
            echo "The 'public' directory does not exist. Please create it.\n";
            return;
        }

        $command = sprintf('php -S %s:%s -t %s', $host, $port, $publicPath);

        echo "Starting development server at http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop the server.\n";

        // Execute the server command
        passthru($command);
    }

    private static function createModel($modelName)
    {
        // Change it to 4 when dependency is applied
        $basePath = dirname(__DIR__, 3); // Root directory of the consuming application
        $modelPath = "{$basePath}/app/Models/{$modelName}.php";

        if (file_exists($modelPath)) {
            echo "Model {$modelName} already exists!\n";
            return;
        }

        $modelContent = "<?php

namespace App\Models;

use App\Core\Models;

class {$modelName} extends Models
{
    //
}";

        if (file_put_contents($modelPath, $modelContent)) {
            echo "Model `{$modelName}` has been successfully created at app/Models/{$modelName}.php\n";

            global $argv;
            if (in_array('-m', $argv)) {
                // Change it to  \joshdevjp\mvccore\Cli\Blueprint::createMigration($modelName); when applying dependency
                \App\Core\Cli\Blueprint::createMigration($modelName);
            }
        } else {
            echo "Failed to create model `{$modelName}`.\n";
        }
    }

    private static function clearCache()
    {
        // Change it to 4
        $basePath = dirname(__DIR__, 3); // Root directory of the consuming application
        $cachePath = "{$basePath}/storage/framework/cache/";
        $viewsPath = "{$basePath}/storage/framework/views/";
        $sessionCachePath = "{$basePath}/storage/framework/sessions/";
        array_map('unlink', glob("$cachePath/*"));
        array_map('unlink', glob("$viewsPath/*"));
        array_map('unlink', glob("$sessionCachePath/*"));

        echo "Cache and compiled views have been cleared!\n";
    }

    private static function help()
    {
        echo "CLI Usage:\n";
        echo "php command create:model ModelName    - Generates a new Model\n";
        echo "php command create:model ModelName -m - Generates a new Model with migration file \n";
        echo "php command cache:clear             - Clears cache and compiled views\n";
        echo "php command migrate                 - Runs database migrations\n";
        echo "php command start [host] [port]     - Starts a local development server (default: localhost:8000)\n";
    }

    private static function runMigrations()
    {
        // Change it to 4
        $basePath = dirname(__DIR__, 3); // Root directory of the consuming application
        $dotenv = \Dotenv\Dotenv::createImmutable($basePath);
        $dotenv->load();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'] ?? 3306,
            $_ENV['DB_NAME']
        );

        try {
            $pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Pass the connection to Schema
            Schema::setConnection($pdo);

            $migrationManager = new MigrationManager($pdo);
            $migrator = new Migrator($migrationManager);

            $migrator->run();
        } catch (\PDOException $e) {
            echo "Database connection failed: " . $e->getMessage() . "\n";
        }
    }
}
