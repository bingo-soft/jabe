<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    HistoricJobLogQueryImpl,
    Page
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypeInterface,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Persistence\AbstractHistoricManager;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\JobInterface;

class HistoricJobLogManager extends AbstractHistoricManager
{
    // select /////////////////////////////////////////////////////////////////

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findHistoricJobLogById(?string $historicJobLogId): ?HistoricJobLogEventEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricJobLog", $historicJobLogId);
    }

    public function findHistoricJobLogsByDeploymentId(?string $deploymentId): array
    {
        return $this->getDbEntityManager()->selectList("selectHistoricJobLogByDeploymentId", $deploymentId);
    }

    public function findHistoricJobLogsByQueryCriteria(HistoricJobLogQueryImpl $query, ?Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricJobLogByQueryCriteria", $query, $page);
    }

    public function findHistoricJobLogsCountByQueryCriteria(HistoricJobLogQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricJobLogCountByQueryCriteria", $query);
    }

    // update ///////////////////////////////////////////////////////////////////

    public function addRemovalTimeToJobLogByRootProcessInstanceId(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricJobLogEventEntity::class, "updateJobLogByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToJobLogByProcessInstanceId(?string $processInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricJobLogEventEntity::class, "updateJobLogByProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToJobLogByBatchId(?string $batchId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["batchId"] = $batchId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricJobLogEventEntity::class, "updateJobLogByBatchId", $parameters);

        $this->getDbEntityManager()
            ->updatePreserveOrder(ByteArrayEntity::class, "updateByteArraysByBatchId", $parameters);
    }

    // delete ///////////////////////////////////////////////////////////////////

    public function deleteHistoricJobLogById(?string $id): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("id", $id);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogById", $id);
        }
    }

    public function deleteHistoricJobLogByJobId(?string $jobId): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("jobId", $jobId);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByJobId", $jobId);
        }
    }

    public function deleteHistoricJobLogsByProcessInstanceIds(array $processInstanceIds): void
    {
        $this->deleteExceptionByteArrayByParameterMap("processInstanceIdIn", $processInstanceIds);
        $this->getDbEntityManager()->deletePreserveOrder(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByProcessInstanceIds", $processInstanceIds);
    }

    public function deleteHistoricJobLogsByProcessDefinitionId(?string $processDefinitionId): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("processDefinitionId", $processDefinitionId);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByProcessDefinitionId", $processDefinitionId);
        }
    }

    public function deleteHistoricJobLogsByDeploymentId(?string $deploymentId): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("deploymentId", $deploymentId);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByDeploymentId", $deploymentId);
        }
    }

    public function deleteHistoricJobLogsByHandlerType(?string $handlerType): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("handlerType", $handlerType);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByHandlerType", $handlerType);
        }
    }

    public function deleteHistoricJobLogsByJobDefinitionId(?string $jobDefinitionId): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("jobDefinitionId", $jobDefinitionId);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByJobDefinitionId", $jobDefinitionId);
        }
    }

    public function deleteHistoricJobLogByBatchIds(array $historicBatchIds): void
    {
        if ($this->isHistoryEnabled()) {
            $this->deleteExceptionByteArrayByParameterMap("historicBatchIdIn", $historicBatchIds);
            $this->getDbEntityManager()->delete(HistoricJobLogEventEntity::class, "deleteHistoricJobLogByBatchIds", $historicBatchIds);
        }
    }

    public function deleteJobLogByRemovalTime(?string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricJobLogEventEntity::class,
                "deleteJobLogByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    // byte array delete ////////////////////////////////////////////////////////

    protected function deleteExceptionByteArrayByParameterMap(?string $key, $value): void
    {
        EnsureUtil::ensureNotNull($key, $key, $value);
        $parameterMap = [];
        $parameterMap[$key] = $value;
        $this->getDbEntityManager()->delete(ByteArrayEntity::class, "deleteExceptionByteArraysByIds", $parameterMap);
    }

    // fire history events ///////////////////////////////////////////////////////

    public function fireJobCreatedEvent(JobInterface $job): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::jobCreate(), $job)) {
            HistoryEventProcessor::processHistoryEvents(new class ($job) extends HistoryEventCreator {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricJobLogCreateEvt($this->job);
                }
            });
        }
    }

    public function fireJobFailedEvent(JobInterface $job, \Throwable $exception): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::jobFail(), $job)) {
            HistoryEventProcessor::processHistoryEvents(new class ($job, $exception) extends HistoryEventCreator {
                private $job;
                private $exception;

                public function __construct(JobInterface $job, \Throwable $exception)
                {
                    $this->job = $job;
                    $this->exception = $exception;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricJobLogFailedEvt($this->job, $this->exception);
                }

                public function postHandleSingleHistoryEventCreated(HistoryEvent $event): void
                {
                    $this->job->setLastFailureLogId($event->getId());
                }
            });
        }
    }

    public function fireJobSuccessfulEvent(JobInterface $job): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::jobSuccess(), $job)) {
            HistoryEventProcessor::processHistoryEvents(new class ($job) extends HistoryEventCreator {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricJobLogSuccessfulEvt($this->job);
                }
            });
        }
    }

    public function fireJobDeletedEvent(JobInterface $job): void
    {
        if ($this->isHistoryEventProduced(HistoryEventTypes::jobDelete(), $job)) {
            HistoryEventProcessor::processHistoryEvents(new class ($job) extends HistoryEventCreator {
                private $job;

                public function __construct(JobInterface $job)
                {
                    $this->job = $job;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricJobLogDeleteEvt($this->job);
                }
            });
        }
    }

    // helper /////////////////////////////////////////////////////////

    protected function isHistoryEventProduced(HistoryEventTypeInterface $eventType, JobInterface $job): bool
    {
        $configuration = Context::getProcessEngineConfiguration();
        $historyLevel = $configuration->getHistoryLevel();
        return $historyLevel->isHistoryEventProduced($eventType, $job);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureHistoricJobLogQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }
}
