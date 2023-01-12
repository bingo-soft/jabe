<?php

namespace Jabe\Impl\Db\Sql;

class VariableTableMapping implements DoctrineTableMappingInterface
{
    public function getTableName(): ?string
    {
        return "ACT_RU_VARIABLE";
    }

    public function getTableAlias(): ?string
    {
        return "V";
    }

    public function isOneToOneRelation(): bool
    {
        return false;
    }
}
