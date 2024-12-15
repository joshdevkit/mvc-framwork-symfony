<?php

namespace App\Core;

use PDO;
use Exception;

abstract class Models implements BaseModel
{
    protected static ?PDO $dbConnection = null;

    protected $fillable = [];
    public $id;

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
        $className = (new \ReflectionClass(static::class))->getShortName();
        $snakeCaseName = static::toSnakeCase($className);
        return static::pluralize($snakeCaseName);
    }

    /**
     * Convert a PascalCase string to snake_case.
     */
    protected static function toSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
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

        return $record;
    }

    /**
     * @method static find
     * @param integer $id
     * @return self|null
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

    /**
     * Find a record by email.
     */
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
     * Check existence of a certain record in the database, excluding a specific ID.
     *
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param int|null $excludeId
     * @return bool
     */
    public static function exists(string $table, string $column, $value, ?int $excludeId = null): bool
    {
        $pdo = self::conn();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
        $params = [$value];

        // Exclude a specific ID if provided
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Update a record in the database. 
     *
     * @param array $attributes 
     * @return bool 
     */
    public function update(array $attributes = []): bool
    {
        $table = static::getTableName();
        $fillable = $this->getFillable();

        // Filter attributes based on fillable properties
        if (!empty($attributes)) {
            $attributes = array_intersect_key($attributes, array_flip($fillable));
        } else {
            // If no attributes are provided, use the current object properties
            $attributes = array_intersect_key(get_object_vars($this), array_flip($fillable));
            unset($attributes['id']); // Ensure we do not update the ID
        }

        // Build the SET clause
        $columns = array_keys($attributes);
        $placeholders = implode(' = ?, ', $columns) . ' = ?';

        // Construct the SQL query
        $sql = "UPDATE {$table} SET {$placeholders} WHERE id = ?";

        // Prepare the values for the query
        $values = array_values($attributes);
        $values[] = $this->id;

        // Execute the query
        $stmt = self::conn()->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Return the fillable properties.
     */
    protected function getFillable(): array
    {
        return $this->fillable;
    }
}
