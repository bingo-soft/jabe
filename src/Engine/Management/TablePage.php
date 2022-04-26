<?php

namespace Jabe\Engine\Management;

class TablePage
{
    protected $tableName;

    /**
     * The total number of rows in the table.
     */
    protected $total = -1;

    /**
     * Identifies the index of the first result stored in this TablePage.
     * For example in a paginated database table, this value identifies the record number of
     * the result on the first row.
     */
    protected $firstResult;

    /**
     * The actual content of the database table, stored as a list of mappings of
     * the form {colum name, value}.
     *
     * This means that every map object in the list corresponds with one row in
     * the database table.
     */
    protected $rowData;

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return the start index of this page
     *         (ie the index of the first element in the page)
     */
    public function getFirstResult(): int
    {
        return $this->firstResult;
    }

    public function setFirstResult(int $firstResult): void
    {
        $this->firstResult = $firstResult;
    }

    public function setRows(array $rowData): void
    {
        $this->rowData = $rowData;
    }

    /**
     * @return the actual table content.
     */
    public function getRows(): array
    {
        return $this->rowData;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return the total rowcount of the table from which this page is only a subset.
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return the actual number of rows in this page.
     */
    public function getSize(): int
    {
        return count($this->rowData);
    }
}
