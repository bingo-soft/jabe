<?php

namespace Jabe\Engine\Impl\Db\Sql;

interface DoctrineTableMappingInterface
{
    public function getTableName(): string;

    public function getTableAlias(): string;

    public function isOneToOneRelation(): bool;
}
