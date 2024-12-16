<?php

namespace App\Core;

use App\Core\Traits\DatabaseOpeartions;
use DateTime;
use PDO;

abstract class Models implements BaseModel
{
    use DatabaseOpeartions;

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
