<?php

namespace App\Core\Cli;

use App\Core\Schema\Column;
use App\Core\Schema\ForeignKey;

class TableBlueprint
{
    private $tableName;
    private $columns = [];
    private $foreignKeys = [];

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    // Define the primary key column (INT UNSIGNED)
    public function id($name = 'id')
    {
        $column = new Column($name, 'BIGINT');
        $column->primary()->autoIncrement()->unsigned();  // Ensure it's unsigned
        $this->columns[$name] = $column;

        return $this;
    }

    public function timestamps()
    {
        // Get the configured timezone from the config/app.php
        $timezone = config('app.timezone');
        date_default_timezone_set($timezone);

        // Define non-nullable created_at column with default CURRENT_TIMESTAMP
        $this->columns['created_at'] = (new Column('created_at', 'TIMESTAMP'))
            ->notNull()
            ->default("CURRENT_TIMESTAMP");

        // Define non-nullable updated_at column with default CURRENT_TIMESTAMP
        $this->columns['updated_at'] = (new Column('updated_at', 'TIMESTAMP'))
            ->notNull()
            ->default("CURRENT_TIMESTAMP");

        return $this;
    }



    public function string($name, $length = 255)
    {
        $column = new Column($name, 'VARCHAR', $length);
        $this->columns[$name] = $column;

        return $column;
    }

    // Define foreign key column as BIGINT UNSIGNED
    public function foreignId($columnName)
    {
        $this->columns[$columnName] = new Column($columnName, 'BIGINT');
        $this->columns[$columnName]->unsigned();  // Make it unsigned
        return $this;
    }


    // Define the foreign key constraint
    public function constrained($referencedTable = null)
    {
        if (!$referencedTable) {
            $columnName = array_key_last($this->columns);
            $referencedTable = $this->pluralizeForm(substr($columnName, 0, -3));
        }

        $this->foreignKeys[] = new ForeignKey($columnName, $referencedTable, 'CASCADE');

        return $this;
    }


    // Allow a column to be nullable
    public function nullable()
    {
        end($this->columns);
        $lastKey = key($this->columns);
        $this->columns[$lastKey]->nullable();

        return $this;
    }

    // Define unique constraints
    public function unique($columnName)
    {
        if (isset($this->columns[$columnName])) {
            $this->columns[$columnName]->unique();
        }
        return $this;
    }

    private function pluralizeForm($word)
    {
        $lowerWord = strtolower($word);

        if (preg_match('/(s|sh|ch)$/', $lowerWord)) {
            return $word . 'es';
        } elseif (preg_match('/y$/', $lowerWord)) {
            return substr($word, 0, -1) . 'ies';
        }

        return $word . 's';
    }

    // Define the onDelete action for foreign keys
    public function onDelete($action)
    {
        if (empty($this->foreignKeys)) {
            throw new \RuntimeException('No foreign key defined.');
        }

        $lastKey = end($this->foreignKeys);
        $lastKey->action = $action;

        return $this;
    }

    // Generate the SQL statement for table creation
    public function toSql()
    {
        $columnsSql = [];
        foreach ($this->columns as $column) {
            $columnsSql[] = $column->toSql();
        }

        $foreignKeysSql = [];
        foreach ($this->foreignKeys as $fk) {
            $foreignKeysSql[] = $fk->toSql();
        }

        return "CREATE TABLE `$this->tableName` (\n    " . implode(",\n    ", array_merge($columnsSql, $foreignKeysSql)) . "\n);";
    }
}
