<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Authorization\Resources;
use BpmPlatform\Engine\History\HistoricTaskInstanceInterface;
use BpmPlatform\Engine\Impl\{
    HistoricTaskInstanceQueryImpl,
    Page
};
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\ListQueryParameterObject;
use BpmPlatform\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricTaskInstanceEventEntity,
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\AbstractHistoricManager;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class HistoricTaskInstanceManager extends AbstractHistoricManager
{
    /**
     * Deletes all data related with tasks, which belongs to specified process instance ids.
     * @param processInstanceIds
     * @param deleteVariableInstances when true, will also delete variable instances. Can be false when variable instances were deleted separately.
     */
    public function deleteHistoricTaskInstancesByProcessInstanceIds(array $processInstanceIds, bool $deleteVariableInstances): void
    {
        $commandContext = Context::getCommandContext();

        if ($deleteVariableInstances) {
            $this->getHistoricVariableInstanceManager()->deleteHistoricVariableInstancesByTaskProcessInstanceIds($processInstanceIds);
        }

        $this->getHistoricDetailManager()
            ->deleteHistoricDetailsByTaskProcessInstanceIds($processInstanceIds);

        $commandContext
            ->getCommentManager()
            ->deleteCommentsByTaskProcessInstanceIds($processInstanceIds);

        $this->getAttachmentManager()
            ->deleteAttachmentsByTaskProcessInstanceIds($processInstanceIds);

        $this->getHistoricIdentityLinkManager()
            ->deleteHistoricIdentityLinksLogByTaskProcessInstanceIds($processInstanceIds);

        $this->getDbEntityManager()->deletePreserveOrder(HistoricTaskInstanceEntity::class, "deleteHistoricTaskInstanceByProcessInstanceIds", $processInstanceIds);
    }

    /*public function deleteHistoricTaskInstancesByCaseInstanceIds(array $caseInstanceIds) {

        CommandContext commandContext = Context::getCommandContext();

        getHistoricDetailManager()
            ->deleteHistoricDetailsByTaskCaseInstanceIds(caseInstanceIds);

        commandContext
            ->getCommentManager()
            ->deleteCommentsByTaskCaseInstanceIds(caseInstanceIds);

        getAttachmentManager()
            ->deleteAttachmentsByTaskCaseInstanceIds(caseInstanceIds);

        getHistoricIdentityLinkManager()
            ->deleteHistoricIdentityLinksLogByTaskCaseInstanceIds(caseInstanceIds);

        getDbEntityManager()->deletePreserveOrder(HistoricTaskInstanceEntity.class, "deleteHistoricTaskInstanceByCaseInstanceIds", caseInstanceIds);
    }*/

    public function findHistoricTaskInstanceCountByQueryCriteria(HistoricTaskInstanceQueryImpl $historicTaskInstanceQuery): int
    {
        if ($this->isHistoryEnabled()) {
            $this->configureQuery($historicTaskInstanceQuery);
            return $this->getDbEntityManager()->selectOne("selectHistoricTaskInstanceCountByQueryCriteria", $historicTaskInstanceQuery);
        }

        return 0;
    }

    public function findHistoricTaskInstancesByQueryCriteria(HistoricTaskInstanceQueryImpl $historicTaskInstanceQuery, Page $page): array
    {
        if ($this->isHistoryEnabled()) {
            $this->configureQuery($historicTaskInstanceQuery);
            return $this->getDbEntityManager()->selectList("selectHistoricTaskInstancesByQueryCriteria", $historicTaskInstanceQuery, $page);
        }

        return [];
    }

    public function findHistoricTaskInstanceById(string $taskId): HistoricTaskInstanceEntity
    {
        EnsureUtil::ensureNotNull("Invalid historic task id", "taskId", $taskId);

        if ($this->isHistoryEnabled()) {
            return $this->getDbEntityManager()->selectOne("selectHistoricTaskInstance", $taskId);
        }

        return null;
    }

    public function deleteHistoricTaskInstanceById(string $taskId): void
    {
        if ($this->isHistoryEnabled()) {
            $historicTaskInstance = findHistoricTaskInstanceById($taskId);
            if ($historicTaskInstance != null) {
                $commandContext = Context::getCommandContext();

                $commandContext
                    ->getHistoricDetailManager()
                    ->deleteHistoricDetailsByTaskId($taskId);

                $commandContext
                    ->getHistoricVariableInstanceManager()
                    ->deleteHistoricVariableInstancesByTaskId($taskId);

                $commandContext
                    ->getCommentManager()
                    ->deleteCommentsByTaskId($taskId);

                $commandContext
                    ->getAttachmentManager()
                    ->deleteAttachmentsByTaskId($taskId);

                $commandContext
                    ->getHistoricIdentityLinkManager()
                    ->deleteHistoricIdentityLinksLogByTaskId($taskId);

                $this->deleteAuthorizations(Resources::historicTask(), $taskId);

                $this->getDbEntityManager()->delete($historicTaskInstance);
            }
        }
    }

    public function findHistoricTaskInstancesByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter(
            "selectHistoricTaskInstanceByNativeQuery",
            $parameterMap,
            $firstResult,
            $maxResults
        );
    }

    public function findHistoricTaskInstanceCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricTaskInstanceCountByNativeQuery", $parameterMap);
    }

    public function updateHistoricTaskInstance(TaskEntity $taskEntity): void
    {
        $configuration = Context::getProcessEngineConfiguration();

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::taskInstanceUpdate(), $taskEntity)) {
            HistoryEventProcessor::processHistoryEvents(new class ($taskEntity) extends HistoryEventCreator {
                private $taskEntity;

                public function __construct(TaskEntity $taskEntity)
                {
                    $this->taskEntity = $taskEntity;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createTaskInstanceUpdateEvt($this->taskEntity);
                }
            });
        }
    }

    public function addRemovalTimeToTaskInstancesByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricTaskInstanceEventEntity::class, "updateHistoricTaskInstancesByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToTaskInstancesByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricTaskInstanceEventEntity::class, "updateHistoricTaskInstancesByProcessInstanceId", $parameters);
    }

    public function markTaskInstanceEnded(string $taskId, string $deleteReason): void
    {
        $configuration = Context::getProcessEngineConfiguration();

        $taskEntity = Context::getCommandContext()
            ->getDbEntityManager()
            ->selectById(TaskEntity::class, $taskId);

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::taskInstanceComplete(), $taskEntity)) {
            HistoryEventProcessor::processHistoryEvents(new class ($taskEntity, $deleteReason) extends HistoryEventCreator {
                private $taskEntity;
                private $deleteReason;

                public function __construct(TaskEntity $taskEntity, string $deleteReason)
                {
                    $this->taskEntity = $taskEntity;
                    $this->deleteReason = $deleteReason;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createTaskInstanceCompleteEvt($this->taskEntity, $this->deleteReason);
                }
            });
        }
    }

    public function createHistoricTask(TaskEntity $task): void
    {
        $configuration = Context::getProcessEngineConfiguration();

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::taskInstanceCreate(), $task)) {
            HistoryEventProcessor::processHistoryEvents(new class ($task) extends HistoryEventCreator {
                private $task;

                public function __construct(TaskEntity $task)
                {
                    $this->task = $task;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createTaskInstanceCreateEvt($this->task);
                }
            });
        }
    }

    protected function configureQuery(HistoricTaskInstanceQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricTaskInstanceQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function deleteHistoricTaskInstancesByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
    {
        $parameters = [];
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        return $this->getDbEntityManager()
            ->deletePreserveOrder(
                HistoricTaskInstanceEntity::class,
                "deleteHistoricTaskInstancesByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}
