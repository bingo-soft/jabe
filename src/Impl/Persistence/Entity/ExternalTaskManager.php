<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\{
    Direction,
    ExternalTaskQueryImpl,
    ExternalTaskQueryProperty,
    ProcessEngineImpl,
    QueryOrderingProperty
};
use Jabe\Impl\Cfg\{
    TransactionListenerInterface,
    TransactionState
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\{
    ClockUtil,
    DatabaseUtil//,
    //ImmutablePair
};

class ExternalTaskManager extends AbstractManager
{
    public static $EXT_TASK_PRIORITY_ORDERING_PROPERTY;

    public function __construct()
    {
        self::extTaskPriorityOrderingProperty();
    }

    public static function extTaskPriorityOrderingProperty(): QueryOrderingProperty
    {
        if (self::$EXT_TASK_PRIORITY_ORDERING_PROPERTY === null) {
            self::$EXT_TASK_PRIORITY_ORDERING_PROPERTY = new QueryOrderingProperty(ExternalTaskQueryProperty::priority(), Direction::descending());
        }
        return self::$EXT_TASK_PRIORITY_ORDERING_PROPERTY;
    }

    public function findExternalTaskById(string $id): ExternalTaskEntity
    {
        return $this->getDbEntityManager()->selectById(ExternalTaskEntity::class, $this->id);
    }

    public function insert(ExternalTaskEntity $externalTask): void
    {
        $this->getDbEntityManager()->insert($externalTask);
        $this->fireExternalTaskAvailableEvent();
    }

    public function delete(ExternalTaskEntity $externalTask): void
    {
        $this->getDbEntityManager()->delete($externalTask);
    }

    public function findExternalTasksByExecutionId(string $id): array
    {
        return $this->getDbEntityManager()->selectList("selectExternalTasksByExecutionId", $id);
    }

    public function findExternalTasksByProcessInstanceId(string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectExternalTasksByProcessInstanceId", $processInstanceId);
    }

    public function selectExternalTasksForTopics(array $queryFilters, int $maxResults, bool $usePriority): array
    {
        if (empty($queryFilters)) {
            return [];
        }

        $parameters = [];
        $parameters["topics"] = $queryFilters;
        $parameters["now"] = ClockUtil::getCurrentTime()->format('c');
        $parameters["applyOrdering"] = $usePriority;
        $orderingProperties = [];
        $orderingProperties[] = self::$EXT_TASK_PRIORITY_ORDERING_PROPERTY;
        $parameters["orderingProperties"] = $orderingProperties;
        $parameters["usesPostgres"] = DatabaseUtil::checkDatabaseType(DbSqlSessionFactory::POSTGRES);

        $parameter = new ListQueryParameterObject($parameters, 0, $maxResults);
        $this->configureQuery($parameter);

        $manager = $this->getDbEntityManager();
        return $manager->selectList("selectExternalTasksForTopics", $parameter);
    }

    public function findExternalTasksByQueryCriteria(ExternalTaskQueryImpl $externalTaskQuery): array
    {
        $this->configureQuery($externalTaskQuery);
        return $this->getDbEntityManager()->selectList("selectExternalTaskByQueryCriteria", $externalTaskQuery);
    }

    public function findExternalTaskIdsByQueryCriteria(ExternalTaskQueryImpl $externalTaskQuery): array
    {
        $this->configureQuery($externalTaskQuery);
        return $this->getDbEntityManager()->selectList("selectExternalTaskIdsByQueryCriteria", $externalTaskQuery);
    }

    public function findDeploymentIdMappingsByQueryCriteria(ExternalTaskQueryImpl $externalTaskQuery): array
    {
        $this->configureQuery($externalTaskQuery);
        return $this->getDbEntityManager()->selectList("selectExternalTaskDeploymentIdMappingsByQueryCriteria", $externalTaskQuery);
    }

    public function findExternalTaskCountByQueryCriteria(ExternalTaskQueryImpl $externalTaskQuery): int
    {
        $this->configureQuery($externalTaskQuery);
        return $this->getDbEntityManager()->selectOne("selectExternalTaskCountByQueryCriteria", $externalTaskQuery);
    }

    public function selectTopicNamesByQuery(ExternalTaskQueryImpl $externalTaskQuery): array
    {
        $this->configureQuery($externalTaskQuery);
        return $this->getDbEntityManager()->selectList("selectTopicNamesByQuery", $externalTaskQuery);
    }

    protected function updateExternalTaskSuspensionState(
        string $processInstanceId,
        string $processDefinitionId,
        string $processDefinitionKey,
        SuspensionState $suspensionState
    ): void {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(
            ExternalTaskEntity::class,
            "updateExternalTaskSuspensionStateByParameters",
            $this->configureParameterizedQuery($parameters)
        );
    }

    public function updateExternalTaskSuspensionStateByProcessInstanceId(string $processInstanceId, SuspensionState $suspensionState): void
    {
        $this->updateExternalTaskSuspensionState($processInstanceId, null, null, $suspensionState);
    }

    public function updateExternalTaskSuspensionStateByProcessDefinitionId(string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $this->updateExternalTaskSuspensionState(null, $processDefinitionId, null, $suspensionState);
    }

    public function updateExternalTaskSuspensionStateByProcessDefinitionKey(string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $this->updateExternalTaskSuspensionState(null, null, $processDefinitionKey, $suspensionState);
    }

    public function updateExternalTaskSuspensionStateByProcessDefinitionKeyAndTenantId(string $processDefinitionKey, string $processDefinitionTenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isProcessDefinitionTenantIdSet"] = true;
        $parameters["processDefinitionTenantId"] = $processDefinitionTenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ExternalTaskEntity::class, "updateExternalTaskSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    protected function configureQuery(ExternalTaskQueryImpl $query): void
    {
        if ($query instanceof ExternalTaskQueryImpl) {
            $this->getAuthorizationManager()->configureExternalTaskQuery($query);
            $this->getTenantManager()->configureQuery($query);
        } elseif ($query instanceof ListQueryParameterObjec) {
            $this->getAuthorizationManager()->configureExternalTaskFetch($query);
            $this->getTenantManager()->configureQuery($query);
        }
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }

    public function fireExternalTaskAvailableEvent(): void
    {
        Context::getCommandContext()
            ->getTransactionContext()
            ->addTransactionListener(
                TransactionState::COMMITTED,
                new class () implements TransactionListenerInterface {
                    public function execute(CommandContext $commandContext)
                    {
                        ProcessEngineImpl::extTaskConditions()->signalAll();
                    }
                }
            );
    }
}
