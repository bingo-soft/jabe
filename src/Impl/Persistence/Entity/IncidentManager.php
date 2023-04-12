<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    IncidentQueryImpl,
    Page
};
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Runtime\IncidentInterface;

class IncidentManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findIncidentsByExecution(?string $id): array
    {
        return $this->getDbEntityManager()->selectList("selectIncidentsByExecutionId", $id);
    }

    public function findIncidentsByProcessInstance(?string $id): array
    {
        return $this->getDbEntityManager()->selectList("selectIncidentsByProcessInstanceId", $id);
    }

    public function findIncidentCountByQueryCriteria(IncidentQueryImpl $incidentQuery): int
    {
        $this->configureQuery($incidentQuery);
        return $this->getDbEntityManager()->selectOne("selectIncidentCountByQueryCriteria", $incidentQuery);
    }

    public function findIncidentById(?string $id): IncidentInterface
    {
        return $this->getDbEntityManager()->selectById(IncidentEntity::class, $id);
    }

    public function findIncidentByConfiguration(?string $configuration): array
    {
        return $this->findIncidentByConfigurationAndIncidentType($configuration, null);
    }

    public function findIncidentByConfigurationAndIncidentType(?string $configuration, ?string $incidentType): array
    {
        $params = [];
        $params["configuration"] = $configuration;
        $params["incidentType"] = $incidentType;
        return $this->getDbEntityManager()->selectList("selectIncidentsByConfiguration", $params);
    }

    public function findIncidentByQueryCriteria(IncidentQueryImpl $incidentQuery, ?Page $page): array
    {
        $this->configureQuery($incidentQuery);
        return $this->getDbEntityManager()->selectList("selectIncidentByQueryCriteria", $incidentQuery, $page);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureIncidentQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }
}
