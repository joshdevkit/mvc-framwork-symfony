<?php

namespace App\Core\Cli;

class TableBlueprint
{
    private $tableName;
    private $columns = [];
    private $foreignKeys = [];
    private $uniqueConstraints = [];

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function id()
    {
        $this->columns[] = "`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
    }

    public function timestamps()
    {
        $this->columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }

    public function string($columnName, $length = 255)
    {
        $this->columns[] = "`$columnName` VARCHAR($length)";
        return $this;
    }

    public function unique()
    {
        $lastColumn = array_pop($this->columns);
        $this->uniqueConstraints[] = $lastColumn;
        $this->columns[] = rtrim($lastColumn) . " UNIQUE";
        return $this;
    }

    public function longText($columnName)
    {
        $this->columns[] = "`$columnName` LONGTEXT";
    }

    public function enum($columnName, $values)
    {
        $values = implode("', '", $values);
        $this->columns[] = "`$columnName` ENUM('$values')";
    }

    public function integer($columnName)
    {
        $this->columns[] = "`$columnName` INT";
    }

    public function integerWithDefault($columnName, $default)
    {
        $this->columns[] = "`$columnName` INT DEFAULT $default";
    }

    public function decimal($columnName, $precision, $scale)
    {
        $this->columns[] = "`$columnName` DECIMAL($precision, $scale)";
    }

    public function decimalWithDefault($columnName, $precision, $scale, $default)
    {
        $this->columns[] = "`$columnName` DECIMAL($precision, $scale) DEFAULT $default";
    }

    public function boolean($columnName)
    {
        $this->columns[] = "`$columnName` BOOLEAN";
    }

    public function booleanWithDefault($columnName, $default)
    {
        $this->columns[] = "`$columnName` BOOLEAN DEFAULT $default";
    }

    public function foreignId($columnName)
    {
        $this->columns[] = "`$columnName` INT UNSIGNED";
        $this->foreignKeys[$columnName] = [
            'references' => 'id',  // Default reference column
            'on' => null,         // Table name will be inferred
            'nullable' => false,  // Default is not nullable
            'onDelete' => null,   // Default no delete action
        ];
        return $this;
    }

    public function constrained($tableName = null)
    {
        $lastKey = array_key_last($this->foreignKeys);
        if ($lastKey !== null) {
            $this->foreignKeys[$lastKey]['on'] = $tableName ?? $this->inferTableName($lastKey);
        }
        return $this;
    }

    public function nullable()
    {
        $lastKey = array_key_last($this->foreignKeys);
        if ($lastKey !== null) {
            $this->foreignKeys[$lastKey]['nullable'] = true;
        }
        return $this;
    }

    public function onDelete($action)
    {
        $lastKey = array_key_last($this->foreignKeys);
        if ($lastKey !== null) {
            $this->foreignKeys[$lastKey]['onDelete'] = $action;
        }
        return $this;
    }

    private function inferTableName($columnName)
    {
        return rtrim($columnName, '_id') . 's'; // Infer table name from column name (e.g., `user_id` -> `users`)
    }

    public function toSql()
    {
        $columnsSql = [];
        $foreignKeysSql = [];
        $definedColumns = [];

        // Define table columns
        foreach ($this->columns as $column) {
            $columnsSql[] = $column;
            $cleanColumn = trim($column, "` ");
            $definedColumns[$cleanColumn] = true;
        }

        // Handle foreign keys separately to avoid duplication
        foreach ($this->foreignKeys as $column => $foreign) {
            if (isset($definedColumns[$column])) {
                $constraint = "FOREIGN KEY (`$column`) REFERENCES `{$foreign['on']}`(`{$foreign['references']}`)";
                if ($foreign['onDelete']) {
                    $constraint .= " ON DELETE {$foreign['onDelete']}";
                }
                $foreignKeysSql[] = $constraint;
            }
        }

        $sqlQuery = "CREATE TABLE `$this->tableName` (\n    " .
            implode(",\n    ", array_merge($columnsSql, $foreignKeysSql)) .
            "\n);";

        return $sqlQuery;
    }
}
