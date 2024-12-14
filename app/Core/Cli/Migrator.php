<?php

namespace App\Core\Cli;

use PDO;

class Migrator
{
    private $migrationManager;
    private $migrationsPath;

    public function __construct(MigrationManager $migrationManager)
    {
        $this->migrationManager = $migrationManager;
        //change it to 4
        $this->migrationsPath = dirname(__DIR__, 3) . '/database/migrations'; // Dynamically calculate the migrations path
    }

    public function run()
    {
        $executedMigrations = $this->migrationManager->getExecutedMigrations();
        $migrationFiles = scandir($this->migrationsPath);

        $batch = $this->getNextBatch();
        $migrationsToRun = [];
        $nothingToMigrate = true;

        foreach ($migrationFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && !in_array($file, $executedMigrations)) {
                $migrationsToRun[] = $file;
            }
        }

        if (empty($migrationsToRun)) {
            echo "Nothing to migrate.\n";
        } else {
            echo "Migrating:\n";
            foreach ($migrationsToRun as $file) {
                $migrationName = pathinfo($file, PATHINFO_FILENAME);
                echo " - $migrationName\n";
                require_once $this->migrationsPath . '/' . $file;
                $className = $this->getClassNameFromFileName($file);
                if (class_exists($className)) {
                    try {
                        $migration = new $className();
                        $migration->up();
                        $this->migrationManager->logMigration($file, $batch);
                        $nothingToMigrate = false;
                    } catch (\Exception $e) {
                        echo "Migration $file failed: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "Class $className does not exist.\n";
                }
            }

            if (!$nothingToMigrate) {
                echo "Migrations completed successfully.\n";
            }
        }
    }

    private function getClassNameFromFileName($file)
    {
        $fileNameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
        $fileNameWithoutDate = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $fileNameWithoutExtension);

        $parts = explode('_', $fileNameWithoutDate);
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        return $className;
    }

    private function getNextBatch()
    {
        $stmt = $this->migrationManager->getPdo()->query("SELECT MAX(`batch`) AS max_batch FROM `migrations`");
        $maxBatch = $stmt->fetch(PDO::FETCH_ASSOC)['max_batch'];
        return $maxBatch ? $maxBatch + 1 : 1;
    }
}
