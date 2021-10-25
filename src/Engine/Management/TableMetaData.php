<?php

namespace BpmPlatform\Engine\Management;

class TableMetaData
{
    protected $tableName;

    protected $columnNames = [];

    protected $columnTypes = [];

    public function __construct(?string $tableName = null)
    {
        $this->tableName = $tableName;
    }

    public function addColumnMetaData(string $columnName, string $columnType): void
    {
        $this->columnNames[] = $columnName;
        $this->columnTypes[] = $columnType;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    public function setColumnNames(array $columnNames): void
    {
        $this->columnNames = $columnNames;
    }

    public function getColumnTypes(): array
    {
        return $this->columnTypes;
    }

    public function setColumnTypes(array $columnTypes): void
    {
        $this->columnTypes = $columnTypes;
    }
}
