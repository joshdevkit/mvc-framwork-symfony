<?php

namespace App\Core\Schema;

class ForeignKey
{
    public $columnName;
    public $referencedTable;
    public $onDelete;

    public function __construct($columnName, $referencedTable, $onDelete = 'CASCADE')
    {
        $this->columnName = $columnName;
        $this->referencedTable = $referencedTable;
        $this->onDelete = $onDelete;
    }

    public function toSql()
    {
        return "CONSTRAINT {$this->referencedTable}_{$this->columnName}_foreign FOREIGN KEY ($this->columnName) REFERENCES {$this->referencedTable}(id) ON DELETE $this->onDelete";
    }
}
