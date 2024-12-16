<?php

namespace App\Core\Schema;

class UniqueConstraint
{
    public $columnName;

    public function __construct($columnName)
    {
        $this->columnName = $columnName;
    }

    public function __toString()
    {
        return "UNIQUE($this->columnName)";
    }
}
