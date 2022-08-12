<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\Resources;
use Jabe\Impl\{
    Page,
    TaskQueryImpl
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\EnsureUtil;

class TaskManager extends AbstractManager
{
    public function insertTask(TaskEntity $task): void
    {
        $this->getDbEntityManager()->insert($task);
        $this->createDefaultAuthorizations($task);
    }

    public function deleteTasksByProcessInstanceId(string $processInstanceId, string $deleteReason, bool $cascade, bool $skipCustomListeners): void
    {
        $tasks = $this->getDbEntityManager()
            ->createTaskQuery()
            ->processInstanceId($processInstanceId)
            ->list();

        $reason = ($deleteReason === null || strlen($deleteReason) == 0) ? TaskEntity::DELETE_REASON_DELETED : $deleteReason;

        foreach ($tasks as $task) {
            $task->delete($reason, $cascade, $skipCustomListeners);
        }
    }

    /*public function deleteTasksByCaseInstanceId(String caseInstanceId, String deleteReason, boolean cascade) {
        List<TaskEntity> tasks = (List) getDbEntityManager()
            .createTaskQuery()
            .caseInstanceId(caseInstanceId)
            .list();

        String reason = (deleteReason === null || deleteReason.length() == 0) ? TaskEntity.DELETE_REASON_DELETED : deleteReason;

        for (TaskEntity task: tasks) {
            task.delete(reason, cascade, false);
        }
    }*/

    public function deleteTask(TaskEntity $task, string $deleteReason, bool $cascade, bool $skipCustomListeners): void
    {
        if (!$task->isDeleted()) {
            $task->setDeleted(true);

            $commandContext = Context::getCommandContext();
            $taskId = $task->getId();

            $subTasks = $this->findTasksByParentTaskId($taskId);
            foreach ($subTasks as $subTask) {
                $subTask->delete($deleteReason, $cascade, $skipCustomListeners);
            }

            $task->deleteIdentityLinks();

            $commandContext
            ->getVariableInstanceManager()
            ->deleteVariableInstanceByTask($task);

            if ($cascade) {
                $commandContext
                    ->getHistoricTaskInstanceManager()
                    ->deleteHistoricTaskInstanceById($taskId);
            } else {
                $commandContext
                    ->getHistoricTaskInstanceManager()
                    ->markTaskInstanceEnded($taskId, $deleteReason);
            }

            $this->deleteAuthorizations(Resources::task(), $taskId);
            $this->getDbEntityManager()->delete($task);
        }
    }

    public function findTaskById(string $id): ?TaskEntity
    {
        EnsureUtil::ensureNotNull("Invalid task id", "id", $this->id);
        return $this->getDbEntityManager()->selectById(TaskEntity::class, $this->id);
    }

    public function findTasksByExecutionId(string $executionId): array
    {
        return $this->getDbEntityManager()->selectList("selectTasksByExecutionId", $executionId);
    }

    /*public function findTaskByCaseExecutionId(string $caseExecutionId): ?TaskEntity
    {
        return (TaskEntity) getDbEntityManager().selectOne("selectTaskByCaseExecutionId", caseExecutionId);
    }*/

    public function findTasksByProcessInstanceId(string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectTasksByProcessInstanceId", $processInstanceId);
    }

    public function findTasksByQueryCriteria(TaskQueryImpl $taskQuery, ?Page $page = null): array
    {
        if ($page === null) {
            $this->configureQuery($taskQuery);
            return $this->getDbEntityManager()->selectList("selectTaskByQueryCriteria", $taskQuery);
        } else {
            $taskQuery->setFirstResult($page->getFirstResult());
            $taskQuery->setMaxResults($page->getMaxResults());
            return $this->findTasksByQueryCriteria($taskQuery);
        }
    }

    public function findTaskCountByQueryCriteria(TaskQueryImpl $taskQuery): int
    {
        $this->configureQuery($taskQuery);
        return $this->getDbEntityManager()->selectOne("selectTaskCountByQueryCriteria", $taskQuery);
    }

    public function findTasksByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectTaskByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findTaskCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectTaskCountByNativeQuery", $parameterMap);
    }

    public function findTasksByParentTaskId(string $parentTaskId): array
    {
        return $this->getDbEntityManager()->selectList("selectTasksByParentTaskId", $parentTaskId);
    }

    public function updateTaskSuspensionStateByProcessDefinitionId(string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(TaskEntity::class, "updateTaskSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateTaskSuspensionStateByProcessInstanceId(string $processInstanceId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(TaskEntity::class, "updateTaskSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateTaskSuspensionStateByProcessDefinitionKey(string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(TaskEntity::class, "updateTaskSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateTaskSuspensionStateByProcessDefinitionKeyAndTenantId(string $processDefinitionKey, string $processDefinitionTenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = true;
        $parameters["processDefinitionTenantId"] = $processDefinitionTenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(TaskEntity::class, "updateTaskSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    /*public void updateTaskSuspensionStateByCaseExecutionId(String caseExecutionId, SuspensionState suspensionState) {
        Map<String, Object> parameters = new HashMap<String, Object>();
        parameters.put("caseExecutionId", caseExecutionId);
        parameters.put("suspensionState", suspensionState.getStateCode());
        getDbEntityManager().update(TaskEntity.class, "updateTaskSuspensionStateByParameters", configureParameterizedQuery(parameters));
    }*/

    // helper ///////////////////////////////////////////////////////////

    protected function createDefaultAuthorizations(TaskEntity $task): void
    {
        if ($this->isAuthorizationEnabled()) {
            $provider = $this->getResourceAuthorizationProvider();
            $authorizations = $provider->newTask($task);
            $this->saveDefaultAuthorizations($authorizations);
        }
    }

    protected function configureQuery(TaskQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureTaskQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }
}
