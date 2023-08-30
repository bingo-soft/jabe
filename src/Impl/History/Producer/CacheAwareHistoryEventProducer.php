<?php

namespace Jabe\Impl\History\Producer;

use Jabe\Delegate\DelegateTaskInterface;
use Jabe\Impl\Batch\BatchEntity;
use Jabe\Impl\Batch\History\HistoricBatchEntity;
use Jabe\Impl\Context\Context;
use Jabe\Impl\History\Event\{
    HistoricActivityInstanceEventEntity,
    HistoryEvent,
    HistoricIncidentEventEntity,
    HistoricProcessInstanceEventEntity,
    HistoricTaskInstanceEventEntity
};
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Runtime\IncidentInterface;

class CacheAwareHistoryEventProducer extends DefaultHistoryEventProducer
{
    protected function loadActivityInstanceEventEntity(ExecutionEntity $execution): HistoricActivityInstanceEventEntity
    {
        $activityInstanceId = $execution->getActivityInstanceId();

        $cachedEntity = $this->findInCache(HistoricActivityInstanceEventEntity::class, $activityInstanceId);

        if ($cachedEntity !== null) {
            return $cachedEntity;
        } else {
            return $this->newActivityInstanceEventEntity($execution);
        }
    }

    protected function loadProcessInstanceEventEntity(ExecutionEntity $execution): HistoricProcessInstanceEventEntity
    {
        $processInstanceId = $execution->getProcessInstanceId();

        $cachedEntity = $this->findInCache(HistoricProcessInstanceEventEntity::class, $processInstanceId);

        if ($cachedEntity !== null) {
            return $cachedEntity;
        } else {
            return $this->newProcessInstanceEventEntity($execution);
        }
    }

    protected function loadTaskInstanceEvent(DelegateTaskInterface $task): HistoricTaskInstanceEventEntity
    {
        $taskId = $task->getId();

        $cachedEntity = $this->findInCache(HistoricTaskInstanceEventEntity::class, $taskId);

        if ($cachedEntity !== null) {
            return $cachedEntity;
        } else {
            return $this->newTaskInstanceEventEntity($task);
        }
    }

    protected function loadIncidentEvent(IncidentInterface $incident): HistoricIncidentEventEntity
    {
        $incidentId = $incident->getId();

        $cachedEntity = $this->findInCache(HistoricIncidentEventEntity::class, $incidentId);

        if ($cachedEntity !== null) {
            return $cachedEntity;
        } else {
            return $this->newIncidentEventEntity($incident);
        }
    }

    protected function loadBatchEntity(BatchEntity $batch): HistoricBatchEntity
    {
        $batchId = $batch->getId();

        $cachedEntity = $this->findInCache(HistoricBatchEntity::class, $batchId);

        if ($cachedEntity !== null) {
            return $cachedEntity;
        } else {
            return $this->newBatchEventEntity($batch);
        }
    }

    /** find a cached entity by primary key */
    protected function findInCache(?string $type, ?string $id): ?HistoryEvent
    {
        return Context::getCommandContext()
        ->getDbEntityManager()
        ->getCachedEntity($type, $id);
    }
}
