<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\{
    JobDefinitionQueryImpl,
    Page
};
use BpmPlatform\Engine\Impl\Db\ListQueryParameterObject;
use BpmPlatform\Engine\Impl\Persistence\AbstractManager;
use BpmPlatform\Engine\Management\JobDefinitionInterface;

class JobDefinitionManager extends AbstractManager
{
    public function findById(string $jobDefinitionId): ?JobDefinitionEntity
    {
        return $this->getDbEntityManager()->selectById(JobDefinitionEntity::class, $jobDefinitionId);
    }

    public function findByProcessDefinitionId(string $processDefinitionId): array
    {
        return $this->getDbEntityManager()->selectList("selectJobDefinitionsByProcessDefinitionId", $processDefinitionId);
    }

    public function deleteJobDefinitionsByProcessDefinitionId(string $id): void
    {
        $this->getDbEntityManager()->delete(JobDefinitionEntity::class, "deleteJobDefinitionsByProcessDefinitionId", $id);
    }

    public function findJobDefnitionByQueryCriteria(JobDefinitionQueryImpl $jobDefinitionQuery, Page $page): array
    {
        $this->configureQuery($jobDefinitionQuery);
        return $this->getDbEntityManager()->selectList("selectJobDefinitionByQueryCriteria", $jobDefinitionQuery, $page);
    }

    public function findJobDefinitionCountByQueryCriteria(JobDefinitionQueryImpl $jobDefinitionQuery): int
    {
        $this->configureQuery($jobDefinitionQuery);
        return $this->getDbEntityManager()->selectOne("selectJobDefinitionCountByQueryCriteria", $jobDefinitionQuery);
    }

    public function updateJobDefinitionSuspensionStateById(string $jobDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["jobDefinitionId"] = $jobDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            JobDefinitionEntity::class,
            "updateJobDefinitionSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    public function updateJobDefinitionSuspensionStateByProcessDefinitionId(string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            JobDefinitionEntity::class,
            "updateJobDefinitionSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    public function updateJobDefinitionSuspensionStateByProcessDefinitionKey(string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            JobDefinitionEntity::class,
            "updateJobDefinitionSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    public function updateJobDefinitionSuspensionStateByProcessDefinitionKeyAndTenantId(string $processDefinitionKey, string $processDefinitionTenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = true;
        $parameters["processDefinitionTenantId"] = $processDefinitionTenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            JobDefinitionEntity::class,
            "updateJobDefinitionSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    protected function configureQuery(JobDefinitionQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureJobDefinitionQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }
}
