<?php

namespace App\Core\Cli;

class Blueprint
{
    /**
     * Create a migration file for a given model.
     *
     * @param string $modelName
     */
    public static function createMigration($modelName)
    {
        // Set the migrations directory
        //change it to 4
        $migrationPath = dirname(__DIR__, 3) . '/database/migrations/';

        // Ensure the directory exists
        if (!self::ensureDirectoryExists($migrationPath)) {
            echo "Failed to ensure migrations directory: $migrationPath\n";
            return;
        }

        // Generate migration file details
        $timestamp = date('Y_m_d_His');
        $tableName = self::pluralize(self::toSnakeCase($modelName));
        $className = 'Create' . self::pluralize(self::toUpperCaseFirst($modelName)) . 'Table';
        $migrationFile = "{$migrationPath}{$timestamp}_create_{$tableName}_table.php";

        // Define the migration file content
        $content = self::generateMigrationContent($className, $tableName);

        // Write the migration file
        if (file_put_contents($migrationFile, $content) === false) {
            echo "Failed to create migration file at $migrationFile\n";
        } else {
            echo "Migration file created: {$timestamp}_create_{$tableName}_table.php\n";
        }
    }

    /**
     * Ensure the given directory exists. Create it if necessary.
     *
     * @param string $path
     * @return bool
     */
    private static function ensureDirectoryExists($path): bool
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true) || is_dir($path);
        }
        return true;
    }

    /**
     * Generate the content of a migration file.
     *
     * @param string $className
     * @param string $tableName
     * @return string
     */
    private static function generateMigrationContent(string $className, string $tableName): string
    {
        return <<<EOD
<?php

use App\Core\Cli\Migration;
use App\Core\Cli\Schema;

class $className extends Migration
{
    public function up()
    {
        Schema::create('$tableName', function (\$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('$tableName');
    }
}
EOD;
    }

    /**
     * Convert a PascalCase string to snake_case.
     *
     * @param string $input
     * @return string
     */
    private static function toSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Pluralize a word (basic pluralization).
     *
     * @param string $word
     * @return string
     */
    private static function pluralize(string $word): string
    {
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        }
        return $word . 's';
    }

    /**
     * Capitalize the first letter of a string.
     *
     * @param string $input
     * @return string
     */
    private static function toUpperCaseFirst(string $input): string
    {
        return ucfirst($input);
    }
}
