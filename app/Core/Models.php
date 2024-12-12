<?php

namespace App\Core;

use PDO;
use Exception;

abstract class Models implements BaseModel
{
    protected static ?PDO $dbConnection = null;

    protected $fillable = [];

    /**
     * Get the database connection (initialize once).
     */
    protected static function conn(): PDO
    {
        if (self::$dbConnection === null) {
            global $config;
            $db = new Database($config);
            self::$dbConnection = $db->connect();
        }

        return self::$dbConnection;
    }

    /**
     * Get the table name based on the class name.
     */
    protected static function getTableName(): string
    {
        $className = strtolower((new \ReflectionClass(static::class))->getShortName());
        return static::pluralize($className);
    }

    /**
     * Pluralize a word (basic pluralization).
     */
    protected static function pluralize(string $word): string
    {
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        }
        return $word . 's';
    }

    /**
     * Insert a record into the database and return an instance of the class.
     */
    protected static function insert(array $attributes): self
    {
        $table = static::getTableName();

        $instance = new static();
        $fillable = $instance->getFillable();

        // Only keep attributes that are in the $fillable array
        $attributes = array_intersect_key($attributes, array_flip($fillable));

        $columns = implode(',', array_keys($attributes));
        $placeholders = implode(',', array_fill(0, count($attributes), '?'));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $stmt = self::conn()->prepare($sql);
        $stmt->execute(array_values($attributes));

        $attributes['id'] = (int) self::conn()->lastInsertId();

        $instance = new static();
        foreach ($attributes as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * Create a new record.
     */
    public static function create(array $attributes): self
    {
        return static::insert($attributes);
    }

    /**
     * Find a record by ID or throw an exception if not found.
     */
    public static function findOrFail(int $id): self
    {
        $record = static::find($id);

        if (!$record) {
            throw new Exception("Record not found with ID: {$id}");
        }

        $instance = new static();
        foreach ($record as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * Find a record by ID or return null.
     */
    public static function find(int $id): ?self
    {
        $table = static::getTableName();

        $sql = "SELECT * FROM {$table} WHERE id = ? LIMIT 1";

        $stmt = self::conn()->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $instance = new static();
        foreach ($result as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }


    /** * Find a record by email. */
    public static function findByEmail(string $email): ?self
    {
        $table = static::getTableName();
        $sql = "SELECT * FROM {$table} WHERE email = ? LIMIT 1";
        $stmt = self::conn()->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }
        $instance = new static();
        foreach ($result as $key => $value) {
            $instance->{$key} = $value;
        }
        return $instance;
    }
    /**
     * Undocumented function
     *
     * @param string $table
     * @param string $column
     * @param [type] $value
     * @return boolean
     */
    public static function exists(string $table, string $column, $value): bool
    {
        $pdo = self::conn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Return the fillable properties.
     */
    protected function getFillable(): array
    {
        return $this->fillable;
    }
}
