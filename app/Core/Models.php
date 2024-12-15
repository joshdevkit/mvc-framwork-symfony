<?php

namespace App\Core;

use DateTime;
use PDO;
use Exception;

abstract class Models implements BaseModel
{
    protected static ?PDO $dbConnection = null;

    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
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
     *
     * This method returns an instance of the class with the attributes populated
     * from the database record. If the record doesn't exist, an exception is thrown.
     *
     * @param int $id The ID of the record to find.
     * @throws Exception If the record is not found.
     * @return self
     */
    public static function findOrFail(int $id): self
    {
        $record = static::find($id);

        if (!$record) {
            throw new Exception("Record not found with ID: {$id}");
        }

        return $record; // Casting is handled in find().
    }

    /**
     * Find a record by ID.
     *
     * This method returns an instance of the class with attributes populated
     * from the database record, or null if the record doesn't exist. Sensitive
     * attributes like `password` are excluded from the result.
     *
     * @param int $id The ID of the record to find.
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
            $value = $instance->castAttributeOnRetrieve($key, $value);
            $instance->{$key} = $value;
        }

        // Unset the password attribute if it exists
        if (isset($instance->password)) {
            unset($instance->password);
        }

        return $instance;
    }

    /**
     * Cast an attribute when retrieving from the database.
     *
     * @param string $key The attribute key.
     * @param mixed $value The raw value from the database.
     * @return mixed The casted value.
     */
    protected function castAttributeOnRetrieve(string $key, $value)
    {
        if (isset($this->casts[$key])) {
            $castType = $this->casts[$key];
            switch ($castType) {
                case 'boolean':
                    return (bool) $value;
                case 'integer':
                    return (int) $value;
                case 'float':
                    return (float) $value;
                case 'hashed': // For hashed attributes, return as is (e.g., password hashes).
                    return $value;
                case 'datetime':
                    return new DateTime($value);
                default:
                    return $value; // Default behavior if no matching cast type is found.
            }
        }

        return $value; // Return raw value if no cast is defined.
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
     * Save the current object's data, ensuring casts are applied before updating.
     */
    public function save(array $attributes = []): bool
    {
        $table = static::getTableName();
        $fillable = $this->getFillable();

        // Apply casting to attributes before saving
        foreach ($this->casts as $attribute => $castType) {
            if (isset($attributes[$attribute])) {
                $attributes[$attribute] = $this->castAttribute($attributes[$attribute], $castType);
            }
        }

        // If specific attributes are provided, only update them
        if (!empty($attributes)) {
            $attributes = array_intersect_key($attributes, array_flip($fillable));
        } else {
            // Otherwise, update only the attributes that are set in the object
            $attributes = array_intersect_key(get_object_vars($this), array_flip($fillable));
        }

        if (empty($attributes)) {
            return false; // No attributes to update
        }

        $columns = array_keys($attributes);
        $placeholders = implode(' = ?, ', $columns) . ' = ?';

        $sql = "UPDATE {$table} SET {$placeholders} WHERE id = ?";

        $stmt = self::conn()->prepare($sql);
        $values = array_values($attributes);
        $values[] = $this->id;

        return $stmt->execute($values);
    }


    public function toArray(): array
    {
        $attributes = get_object_vars($this);

        foreach ($this->hidden as $hiddenAttribute) {
            unset($attributes[$hiddenAttribute]);
        }

        foreach ($this->casts as $attribute => $castType) {
            if (isset($attributes[$attribute])) {
                $attributes[$attribute] = $this->castAttribute($attributes[$attribute], $castType);
            }
        }

        return $attributes;
    }



    /**
     * Cast an attribute to the specified type.
     */
    protected function castAttribute($value, string $type)
    {
        switch ($type) {
            case 'hashed':
                return password_hash($value, PASSWORD_DEFAULT);
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value;
            case 'string':
                return (string) $value;
            default:
                return $value;
        }
    }




    /**
     * Return the fillable properties.
     */
    protected function getFillable(): array
    {
        return $this->fillable;
    }
}
