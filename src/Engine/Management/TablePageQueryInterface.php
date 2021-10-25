<?php

namespace BpmPlatform\Engine\Management;

interface TablePageQueryInterface
{
    /**
     * The name of the table of which a page must be fetched.
     */
    public function tableName(string $tableName): TablePageQueryInterface;

    /**
     * Orders the resulting table page rows by the given column in ascending order.
     */
    public function orderAsc(string $column): TablePageQueryInterface;

    /**
     * Orders the resulting table page rows by the given column in descending order.
     */
    public function orderDesc(string $column): TablePageQueryInterface;

    /**
     * Executes the query and returns the {@link TablePage}
     */
    public function listPage(int $firstResult, int $maxResults): TablePage;
}
