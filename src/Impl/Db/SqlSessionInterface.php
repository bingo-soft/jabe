<?php

namespace Jabe\Impl\Db;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

interface SqlSessionInterface
{
    public function selectOne(string $statement, array $params = [], array $types = []);

    public function selectList(string $statement, array $params = [], array $types = [], RowBounds $rowBounds = null);

    public function selectMap(string $statement, array $params = [], array $types = [], string $mapKey = 'id', RowBounds $rowBounds = null);

    public function selectCursor(string $statement, array $params = [], array $types = [], RowBounds $rowBounds = null);

    public function select(string $statement, array $params = [], array $types = [], RowBounds $rowBounds = null, ResultHandlerInterface $handler = null);

    public function insert(string $tableOrStatement, $data, array $types = []);

    public function update(string $tableOrStatement, $data, array $criteria = [], array $types = []);

    public function delete(string $tableOrStatement, array $criteria = [], array $types = []);

    public function commit(): void;

    public function rollback(): void;

    //public function flushStatements(): void;

    public function close(): void;

    //public function clearCache(): void;

    public function getConfiguration(): Configuration;

    public function getConnection(): Connection;

    //public function getMapper(string $type);
}
