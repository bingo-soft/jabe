<?php

namespace Jabe\Engine\Impl\Db\Sql;

class ProcessDefinitionTableMapping implements DoctrineTableMappingInterface
{
    public function getTableName(): string
    {
        return "ACT_RE_PROCDEF";
    }

    public function getTableAlias(): string
    {
        return "P";
    }

    public function isOneToOneRelation(): bool
    {
        return true;
    }
}
