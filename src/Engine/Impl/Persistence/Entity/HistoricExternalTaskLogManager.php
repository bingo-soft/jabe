<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\ExternalTask\ExternalTaskInterface;
use BpmPlatform\Engine\History\HistoricExternalTaskLogInterface;
use BpmPlatform\Engine\Impl\{
    HistoricExternalTaskLogQueryImpl,
    Page
};
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\ListQueryParameterObject;
use BpmPlatform\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypeInterface,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Persistence\AbstractManager;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class HistoricExternalTaskLogManager extends AbstractManager
{
    public function findHistoricExternalTaskLogById(string $historicExternalTaskLogId): ?HistoricExternalTaskLogEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricExternalTaskLog", $historicExternalTaskLogId);
    }

    public function findHistoricExternalTaskLogsByQueryCriteria(HistoricExternalTaskLogQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricExternalTaskLogByQueryCriteria", $query, $page);
    }

    public function findHistoricExternalTaskLogsCountByQueryCriteria(HistoricExternalTaskLogQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricExternalTaskLogCountByQueryCriteria", $query);
    }

    public function addRemovalTimeToExternalTaskLogByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricExternalTaskLogEntity::class, "updateExternalTaskLogByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToExternalTaskLogByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricExternalTaskLogEntity::class, "updateExternalTaskLogByProcessInstanceId", $parameters);
    }

    public function deleteHistoricExternalTaskLogsByProcessInstanceIds(array $processInstanceIds): void
    {
        $this->deleteExceptionByteArrayByParameterMap("processInstanceIdIn", $processInstanceIds);
        $this->getDbEntityManager()->deletePreserveOrder(HistoricExternalTaskLogEntity::class, "deleteHistoricExternalTaskLogByProcessInstanceIds", $processInstanceIds);
    }

    public function deleteExternalTaskLogByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricExternalTaskLogEntity::class,
                "deleteExternalTaskLogByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    protected function deleteExceptionByteArrayByParameterMap(string $key, $value): void
    {
        EnsureUtil::ensureNotNull($key, $value);
        $parameterMap = [];
        $parameterMap[$key] = $value;
        $this->getDbEntityManager()->delete(ByteArrayEntity::class, "deleteErrorDetailsByteArraysByIds", $parameterMap);
    }

    public function fireExternalTaskCreatedEvent(ExternalTaskInterface $externalTask): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::externalTaskCreate(), $externalTask)) {
            HistoryEventProcessor::processHistoryEvents(new class ($externalTask) extends HistoryEventCreator {
                private $externalTask;

                public function __construct(ExternalTaskInterface $externalTask)
                {
                    $this->externalTask = $externalTask;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricExternalTaskLogCreatedEvt($this->externalTask);
                }
            });
        }
    }

    public function fireExternalTaskFailedEvent(ExternalTaskInterface $externalTask): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::externalTaskFail(), $externalTask)) {
            HistoryEventProcessor::processHistoryEvents(new class ($externalTask) extends HistoryEventCreator {
                private $externalTask;

                public function __construct(ExternalTaskInterface $externalTask)
                {
                    $this->externalTask = $externalTask;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricExternalTaskLogFailedEvt($this->externalTask);
                }

                public function postHandleSingleHistoryEventCreated(HistoryEvent $event): void
                {
                    $this->externalTask->setLastFailureLogId($event->getId());
                }
            });
        }
    }

    public function fireExternalTaskSuccessfulEvent(ExternalTaskInterface $externalTask): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::externalTaskSuccess(), $externalTask)) {
            HistoryEventProcessor::processHistoryEvents(new class () extends HistoryEventCreator {
                private $externalTask;

                public function __construct(ExternalTaskInterface $externalTask)
                {
                    $this->externalTask = $externalTask;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricExternalTaskLogSuccessfulEvt($externalTask);
                }
            });
        }
    }

    public function fireExternalTaskDeletedEvent(ExternalTaskInterface $externalTask): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::externalTaskDelete(), $externalTask)) {
            HistoryEventProcessor::processHistoryEvents(new class () extends HistoryEventCreator {
                private $externalTask;

                public function __construct(ExternalTaskInterface $externalTask)
                {
                    $this->externalTask = $externalTask;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricExternalTaskLogDeletedEvt($externalTask);
                }
            });
        }
    }

    protected function isHistoryEventProduced(HistoryEventTypeInterface $eventType, ExternalTaskInterface $externalTask): bool
    {
        $configuration = Context::getProcessEngineConfiguration();
        $historyLevel = $configuration->getHistoryLevel();
        return $historyLevel->isHistoryEventProduced($eventType, $externalTask);
    }

    protected function configureQuery(HistoricExternalTaskLogQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricExternalTaskLogQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }
}
