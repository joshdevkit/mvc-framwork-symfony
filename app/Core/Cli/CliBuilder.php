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

            default:
                self::help();
                break;
        }
    }

    private static function createModel($modelName)
    {
        //change it to 4 when dependency is applied
        $basePath = dirname(__DIR__, 3); // Root directory of the consuming application
        $modelPath = "{$basePath}/app/Models/{$modelName}.php";

        if (file_exists($modelPath)) {
            echo "Model {$modelName} already exists!\n";
            return;
        }

        $modelContent = "<?php

namespace App\Models;

use joshdevjp\mvccore\Models;

class {$modelName} extends Models
{
    //
}";

        if (file_put_contents($modelPath, $modelContent)) {
            echo "Model `{$modelName}` has been successfully created at app/Models/{$modelName}.php\n";

            global $argv;
            if (in_array('-m', $argv)) {
                //change it into  \joshdevjp\mvccore\Cli\Blueprint::createMigration($modelName); when applying dependency
                \App\Core\Cli\Blueprint::createMigration($modelName);
            }
        } else {
            echo "Failed to create model `{$modelName}`.\n";
        }
    }

    private static function clearCache()
    {
        //change it to 4
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
        echo "php create:model ModelName    - Generates a new Model\n";
        echo "php create:model ModelName -m - Generates a new Model with migration file \n";
        echo "php cache:clear             - Clears cache and compiled views\n";
        echo "php migrate                 - Runs database migrations\n";
    }

    private static function runMigrations()
    {
        //change it to 4
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
