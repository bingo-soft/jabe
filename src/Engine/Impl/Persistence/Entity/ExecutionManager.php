<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Authorization\Resources;
use BpmPlatform\Engine\Impl\{
    AbstractQuery,
    ExecutionQueryImpl,
    Page,
    ProcessEngineLogger,
    ProcessInstanceQueryImpl
};
use BpmPlatform\Engine\Impl\Cfg\Auth\ResourceAuthorizationProviderInterface;
use BpmPlatform\Engine\Impl\Db\{
    EnginePersistenceLogger,
    ListQueryParameterObject
};
use BpmPlatform\Engine\Impl\Persistence\AbstractManager;
use BpmPlatform\Engine\Impl\Util\ImmutablePair;
use BpmPlatform\Engine\Runtime\{
    ExecutionInterface,
    ProcessInstanceInterface
};

class ExecutionManager extends AbstractManager
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public function insertExecution(ExecutionEntity $execution): void
    {
        $this->getDbEntityManager()->insert($execution);
        $this->createDefaultAuthorizations($execution);
    }

    public function deleteExecution(ExecutionEntity $execution): void
    {
        $this->getDbEntityManager()->delete($execution);
        if ($execution->isProcessInstanceExecution()) {
            $this->deleteAuthorizations(Resources::processInstance(), $execution->getProcessInstanceId());
        }
    }

    public function deleteProcessInstancesByProcessDefinition(string $processDefinitionId, string $deleteReason, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings): void
    {
        $processInstanceIds = $this->getDbEntityManager()
            ->selectList("selectProcessInstanceIdsByProcessDefinitionId", $processDefinitionId);

        foreach ($processInstanceIds as $processInstanceId) {
            $this->deleteProcessInstance($processInstanceId, $deleteReason, $cascade, $skipCustomListeners, false, $skipIoMappings, false);
        }

        if ($cascade) {
            $this->getHistoricProcessInstanceManager()->deleteHistoricProcessInstanceByProcessDefinitionId($processDefinitionId);
        }
    }

    public function deleteProcessInstance(string $processInstanceId, string $deleteReason, ?bool $cascade = false, ?bool $skipCustomListeners = false, ?bool $skipSubprocesses = false): void
    {
        $execution = $this->findExecutionById($processInstanceId);

        if ($execution == null) {
            //throw LOG.requestedProcessInstanceNotFoundException(processInstanceId);
            throw new \Exception("Execution");
        }

        $this->getTaskManager()->deleteTasksByProcessInstanceId($processInstanceId, $deleteReason, $cascade, $skipCustomListeners);

        // delete the execution BEFORE we delete the history, otherwise we will produce orphan HistoricVariableInstance instances
        $execution->deleteCascade($deleteReason, $skipCustomListeners, $skipIoMappings, $externallyTerminated, $skipSubprocesses);

        if ($cascade) {
            $this->getHistoricProcessInstanceManager()->deleteHistoricProcessInstanceByIds([$processInstanceId]);
        }
    }

    public function findSubProcessInstanceBySuperExecutionId(string $superExecutionId): ?ExecutionEntity
    {
        return $this->getDbEntityManager()->selectOne("selectSubProcessInstanceBySuperExecutionId", $superExecutionId);
    }

    public function findSubProcessInstanceBySuperCaseExecutionId(string $superCaseExecutionId): ?ExecutionEntity
    {
        return $this->getDbEntityManager()->selectOne("selectSubProcessInstanceBySuperCaseExecutionId", $superCaseExecutionId);
    }

    public function findChildExecutionsByParentExecutionId(string $parentExecutionId): array
    {
        return $this->getDbEntityManager()->selectList("selectExecutionsByParentExecutionId", $parentExecutionId);
    }

    public function findExecutionsByProcessInstanceId(string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectExecutionsByProcessInstanceId", $processInstanceId);
    }

    public function findExecutionById(string $executionId): ?ExecutionEntity
    {
        return $this->getDbEntityManager()->selectById(ExecutionEntity::class, $executionId);
    }

    public function findExecutionCountByQueryCriteria(ExecutionQueryImpl $executionQuery): int
    {
        $this->configureQuery($executionQuery);
        return $this->getDbEntityManager()->selectOne("selectExecutionCountByQueryCriteria", $executionQuery);
    }

    public function findExecutionsByQueryCriteria(ExecutionQueryImpl $executionQuery, Page $page): array
    {
        $this->configureQuery($executionQuery);
        return $this->getDbEntityManager()->selectList("selectExecutionsByQueryCriteria", $executionQuery, $page);
    }

    public function findProcessInstanceCountByQueryCriteria(ProcessInstanceQueryImpl $processInstanceQuery): int
    {
        $this->configureQuery($processInstanceQuery);
        return $this->getDbEntityManager()->selectOne("selectProcessInstanceCountByQueryCriteria", $processInstanceQuery);
    }

    public function findProcessInstancesByQueryCriteria(ProcessInstanceQueryImpl $processInstanceQuery, Page $page): array
    {
        $this->configureQuery($processInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectProcessInstanceByQueryCriteria", $processInstanceQuery, $page);
    }

    public function findProcessInstancesIdsByQueryCriteria(ProcessInstanceQueryImpl $processInstanceQuery): array
    {
        $this->configureQuery($processInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectProcessInstanceIdsByQueryCriteria", $processInstanceQuery);
    }

    public function findDeploymentIdMappingsByQueryCriteria(ProcessInstanceQueryImpl $processInstanceQuery): array
    {
        $this->configureQuery($processInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectProcessInstanceDeploymentIdMappingsByQueryCriteria", $processInstanceQuery);
    }

    public function findEventScopeExecutionsByActivityId(string $activityRef, string $parentExecutionId): array
    {
        $parameters = [];
        $parameters["activityId"] = $activityRef;
        $parameters["parentExecutionId"] = $parentExecutionId;
        return $this->getDbEntityManager()->selectList("selectExecutionsByParentExecutionId", $parameters);
    }

    public function findExecutionsByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectExecutionByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findProcessInstanceByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectExecutionByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findExecutionCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectExecutionCountByNativeQuery", $parameterMap);
    }

    public function updateExecutionSuspensionStateByProcessDefinitionId(string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ExecutionEntity::class, "updateExecutionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateExecutionSuspensionStateByProcessInstanceId(string $processInstanceId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ExecutionEntity::class, "updateExecutionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateExecutionSuspensionStateByProcessDefinitionKey(string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ExecutionEntity::class, "updateExecutionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateExecutionSuspensionStateByProcessDefinitionKeyAndTenantId(string $processDefinitionKey, string $tenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isTenantIdSet"] = true;
        $parameters["tenantId"] = $tenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            ExecutionEntity::class,
            "updateExecutionSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    // helper ///////////////////////////////////////////////////////////

    protected function createDefaultAuthorizations(ExecutionEntity $execution): void
    {
        if ($execution->isProcessInstanceExecution() && $this->isAuthorizationEnabled()) {
            $provider = $this->getResourceAuthorizationProvider();
            $authorizations = $provider->newProcessInstance($execution);
            $this->saveDefaultAuthorizations($authorizations);
        }
    }

    protected function configureQuery(AbstractQuery $query): void
    {
        $this->getAuthorizationManager()->configureExecutionQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }
}
