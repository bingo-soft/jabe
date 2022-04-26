<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\AuthorizationException;
use Jabe\Engine\Impl\{
    Page,
    SchemaLogQueryImpl
};
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Management\{
    SchemaLogEntryInterface,
    SchemaLogQueryInterface
};

class SchemaLogManager extends AbstractManager
{
    public function findSchemaLogEntryCountByQueryCriteria(SchemaLogQueryInterface $schemaLogQuery): int
    {
        if ($this->isAuthorized()) {
            return $this->getDbEntityManager()->selectOne("selectSchemaLogEntryCountByQueryCriteria", $schemaLogQuery);
        } else {
            return 0;
        }
    }

    public function findSchemaLogEntriesByQueryCriteria(SchemaLogQueryImpl $schemaLogQueryImpl, Page $page): array
    {
        if ($this->isAuthorized()) {
            return $this->getDbEntityManager()->selectList("selectSchemaLogEntryByQueryCriteria", $schemaLogQueryImpl, $page);
        } else {
            return [];
        }
    }

    private function isAuthorized(): bool
    {
        try {
            $this->getAuthorizationManager()->checkAdminOrPermission("checkReadSchemaLog");
            return true;
        } catch (AuthorizationException $e) {
            return false;
        }
    }
}
