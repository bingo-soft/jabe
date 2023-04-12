<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\AuthorizationException;
use Jabe\Impl\{
    Page,
    SchemaLogQueryImpl
};
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Management\{
    SchemaLogEntryInterface,
    SchemaLogQueryInterface
};

class SchemaLogManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findSchemaLogEntryCountByQueryCriteria(SchemaLogQueryInterface $schemaLogQuery): int
    {
        if ($this->isAuthorized()) {
            return $this->getDbEntityManager()->selectOne("selectSchemaLogEntryCountByQueryCriteria", $schemaLogQuery);
        } else {
            return 0;
        }
    }

    public function findSchemaLogEntriesByQueryCriteria(SchemaLogQueryImpl $schemaLogQueryImpl, ?Page $page): array
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
