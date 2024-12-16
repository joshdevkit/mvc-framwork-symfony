<?php

namespace App\Core\Schema;

class Schema
{
    private $tableName;
    private $columns = [];
    private $foreignKeys = [];
    private $uniqueConstraints = [];
    private $engine = 'InnoDB';
    private $charset = 'utf8mb4';

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function engine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    public function charset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function string($name, $length = 255)
    {
        $column = new Column($name, 'VARCHAR', $length);
        $this->columns[$name] = $column;

        return $column;
    }

    public function foreignId($columnName)
    {
        $referencedTable = rtrim(preg_replace('/_id$/', '', $columnName), '_');
        $referencedTable = $this->pluralizeForm($referencedTable);

        $foreignKey = new ForeignKey($columnName, $referencedTable);

        $this->foreignKeys[] = $foreignKey;

        return $this;
    }

    public function nullable()
    {
        end($this->columns);
        $lastKey = key($this->columns);
        $this->columns[$lastKey]->nullable();

        return $this;
    }

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

        $sql = "CREATE TABLE $this->tableName (\n    " . implode(",\n    ", array_merge($columnsSql, $foreignKeysSql)) . "\n) ENGINE={$this->engine} CHARSET={$this->charset};";

        echo $sql;
    }
}
