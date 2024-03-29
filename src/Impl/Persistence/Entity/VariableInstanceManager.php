<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    Page,
    VariableInstanceQueryImpl
};
use Jabe\Impl\Persistence\AbstractManager;

class VariableInstanceManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findVariableInstancesByTaskId(?string $taskId): array
    {
        return $this->findVariableInstancesByTaskIdAndVariableNames($taskId, null);
    }

    public function findVariableInstancesByTaskIdAndVariableNames(?string $taskId, ?array $variableNames = []): array
    {
        $parameter = [];
        $parameter["taskId"] = $taskId;
        $parameter["variableNames"] = $variableNames;
        return $this->getDbEntityManager()->selectList("selectVariablesByTaskId", $parameter);
    }

    public function findVariableInstancesByExecutionId(?string $executionId): array
    {
        return $this->findVariableInstancesByExecutionIdAndVariableNames($executionId, null);
    }

    public function findVariableInstancesByExecutionIdAndVariableNames(?string $executionId, ?array $variableNames = []): array
    {
        $parameter = [];
        $parameter["executionId"] = $executionId;
        $parameter["variableNames"] = $variableNames;
        return $this->getDbEntityManager()->selectList("selectVariablesByExecutionId", $parameter);
    }

    public function findVariableInstancesByProcessInstanceId(?string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectVariablesByProcessInstanceId", $processInstanceId);
    }

    /*public function findVariableInstancesByCaseExecutionId(String caseExecutionId) {
        return findVariableInstancesByCaseExecutionIdAndVariableNames(caseExecutionId, null);
    }

    public List<VariableInstanceEntity> findVariableInstancesByCaseExecutionIdAndVariableNames(String caseExecutionId, Collection<String> variableNames) {
        Map<String, Object> parameter = new HashMap<String, Object>();
        parameter.put("caseExecutionId", caseExecutionId);
        parameter.put("variableNames", variableNames);
        return getDbEntityManager().selectList("selectVariablesByCaseExecutionId", parameter);
    }*/

    public function deleteVariableInstanceByTask(TaskEntity $task): void
    {
        $variableInstances = $task->getVariableStore()->getVariables();
        foreach ($variableInstances as $variableInstance) {
            $variableInstance->delete();
        }
    }

    public function findVariableInstanceCountByQueryCriteria(VariableInstanceQueryImpl $variableInstanceQuery): int
    {
        $this->configureQuery($variableInstanceQuery);
        return $this->getDbEntityManager()->selectOne("selectVariableInstanceCountByQueryCriteria", $variableInstanceQuery);
    }

    public function findVariableInstanceByQueryCriteria(VariableInstanceQueryImpl $variableInstanceQuery, ?Page $page): array
    {
        $this->configureQuery($variableInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectVariableInstanceByQueryCriteria", $variableInstanceQuery, $page);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureVariableInstanceQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function findVariableInstancesByBatchId(?string $batchId): array
    {
        $parameters = ["batchId" => $batchId];
        return $this->getDbEntityManager()->selectList("selectVariableInstancesByBatchId", $parameters);
    }
}
