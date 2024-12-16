<?php

namespace App\Core\Cli;

class Schema
{
    /** @var \PDO */
    private static $pdo;

    /**
     * Set the PDO connection dynamically.
     *
     * @param \PDO $connection
     */
    public static function setConnection(\PDO $connection)
    {
        self::$pdo = $connection;
    }

    /**
     * Get the PDO connection.
     *
     * @return \PDO|null
     */
    public static function getConnection()
    {
        return self::$pdo;
    }


    /**
     * Drop table if it exists
     *
     * @param string $tableName
     */
    public static function dropIfExists($tableName)
    {
        if (!self::$pdo) {
            echo "Database connection not set.\n";
            return;
        }

        $sql = "DROP TABLE IF EXISTS `$tableName`;";

        self::execute($sql);
    }

    /**
     * Create a table dynamically by generating SQL.
     *
     * @param string $tableName
     * @param callable $callback
     */
    public static function create($tableName, callable $callback)
    {
        if (!self::$pdo) {
            echo "Database connection not set.\n";
            return;
        }

        $table = new TableBlueprint($tableName);
        $callback($table);

        $sql = $table->toSql();

        // echo "Creating table: $tableName\nSQL Generated:\n$sql\n";
        self::execute($sql);
    }

    /**
     * Execute the provided SQL statement.
     *
     * @param string $sql
     */
    private static function execute($sql)
    {

        try {
            self::$pdo->exec($sql);
            // echo "SQL executed successfully.\n";
        } catch (\PDOException $e) {
            echo "Database Error: " . $e->getMessage() . "\n";
        }
    }
}
