<?php

namespace App\Core\Cli;

use PDO;

class MigrationManager
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL
            );
        ");
    }

    public function getExecutedMigrations()
    {
        $stmt = $this->pdo->query("SELECT `migration` FROM `migrations`");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function logMigration($migration, $batch)
    {
        $stmt = $this->pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (:migration, :batch)");
        $stmt->execute(['migration' => $migration, 'batch' => $batch]);
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
