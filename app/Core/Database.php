<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $conn = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Establish and return the database connection.
     */
    public function connect(): PDO
    {
        if (self::$conn === null) {
            try {
                $dsn = 'mysql:host=' . $this->config['host'] . ';dbname=' . $this->config['dbname'];
                self::$conn = new PDO($dsn, $this->config['user'], $this->config['pass']);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$conn;
    }
}
