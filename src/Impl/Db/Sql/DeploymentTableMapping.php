<?php

namespace Jabe\Impl\Db\Sql;

class DeploymentTableMapping implements DoctrineTableMappingInterface
{
    public function getTableName(): ?string
    {
        return "ACT_RE_DEPLOYMENT";
    }

    public function getTableAlias(): ?string
    {
        return "DEP";
    }

    public function isOneToOneRelation(): bool
    {
        return true;
    }
}
