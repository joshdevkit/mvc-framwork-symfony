<?php

namespace App\Core\Schema;

class Column
{
    private $name;
    private $type;
    private $length;
    private $isPrimary = false;
    private $autoIncrement = false;
    private $nullable = false;
    private $unique = false;
    private $unsigned = false;
    private $default = null;

    public function __construct($name, $type, $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    public function primary()
    {
        $this->isPrimary = true;
        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;
        return $this;
    }

    public function nullable()
    {
        $this->nullable = true;
        return $this;
    }

    public function notNull()
    {
        $this->nullable = false;
        return $this;
    }

    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    public function unique()
    {
        $this->unique = true;
        return $this;
    }

    public function unsigned()
    {
        $this->unsigned = true;
        return $this;
    }

    public function toSql()
    {
        $sql = "`$this->name` $this->type";

        if ($this->length) {
            $sql .= "($this->length)";
        }

        if ($this->unsigned) {
            $sql .= " UNSIGNED";
        }

        if ($this->isPrimary) {
            $sql .= " PRIMARY KEY";
        }

        if ($this->autoIncrement) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($this->unique) {
            $sql .= " UNIQUE";
        }

        if ($this->nullable) {
            $sql .= " NULL";
        } else {
            $sql .= " NOT NULL";
        }

        // Handle the default value
        if ($this->default !== null) {
            if (strtoupper($this->default) === "CURRENT_TIMESTAMP") {
                $sql .= " DEFAULT $this->default";
            } else {
                $sql .= " DEFAULT '$this->default'";
            }
        }

        return $sql;
    }
}
