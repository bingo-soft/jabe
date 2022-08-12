<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\{
    CleanableHistoricBatchReportImpl,
    Direction,
    Page,
    QueryOrderingProperty,
    QueryPropertyImpl
};
use Jabe\Impl\Batch\BatchEntity;
use Jabe\Impl\Batch\History\{
    HistoricBatchEntity,
    HistoricBatchQueryImpl
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\ClockUtil;

class HistoricBatchManager extends AbstractManager
{
    public function findBatchCountByQueryCriteria(HistoricBatchQueryImpl $historicBatchQuery): int
    {
        $this->configureQuery($historicBatchQuery);
        return $this->getDbEntityManager()->selectOne("selectHistoricBatchCountByQueryCriteria", $historicBatchQuery);
    }

    public function findBatchesByQueryCriteria(HistoricBatchQueryImpl $historicBatchQuery, Page $page): array
    {
        $this->configureQuery($historicBatchQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricBatchesByQueryCriteria", $historicBatchQuery, $page);
    }

    public function findHistoricBatchById(string $batchId): ?HistoricBatchEntity
    {
        return $this->getDbEntityManager()->selectById(HistoricBatchEntity::class, $batchId);
    }

    public function findHistoricBatchByJobId(string $jobId): ?HistoricBatchEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricBatchByJobId", $jobId);
    }

    public function findHistoricBatchIdsForCleanup(int $batchSize, array $batchOperationsForHistoryCleanup, int $minuteFrom, int $minuteTo): array
    {
        $queryParameters = [];
        $queryParameters["currentTimestamp"] = ClockUtil::getCurrentTime()->format('c');
        $queryParameters["map"]  = $batchOperationsForHistoryCleanup;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $queryParameters["minuteFrom"] = $minuteFrom;
            $queryParameters["minuteTo"] = $minuteTo;
        }
        $parameterObject = new ListQueryParameterObject($queryParameters, 0, $batchSize);
        $parameterObject->addOrderingProperty(new QueryOrderingProperty(new QueryPropertyImpl("END_TIME_"), Direction::ascending()));

        return $this->getDbEntityManager()->selectList("selectHistoricBatchIdsForCleanup", $parameterObject);
    }

    public function deleteHistoricBatchById(string $id): void
    {
        $this->getDbEntityManager()->delete(HistoricBatchEntity::class, "deleteHistoricBatchById", $id);
    }

    public function deleteHistoricBatchesByIds(array $historicBatchIds): void
    {
        $commandContext = Context::getCommandContext();

        $commandContext->getHistoricIncidentManager()->deleteHistoricIncidentsByBatchId($historicBatchIds);
        $commandContext->getHistoricJobLogManager()->deleteHistoricJobLogByBatchIds($historicBatchIds);

        $this->getDbEntityManager()->deletePreserveOrder(HistoricBatchEntity::class, "deleteHistoricBatchByIds", $historicBatchIds);
    }

    public function createHistoricBatch(BatchEntity $batch): void
    {
        $configuration = Context::getProcessEngineConfiguration();

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::batchStart(), $batch)) {
            HistoryEventProcessor::processHistoryEvents(new class ($batch) extends HistoryEventCreator {
                private $batch;

                public function __construct(BatchEntity $batch)
                {
                    $this->batch = $batch;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createBatchStartEvent($this->batch);
                }
            });
        }
    }

    public function completeHistoricBatch(BatchEntity $batch): void
    {
        $configuration = Context::getProcessEngineConfiguration();

        $historyLevel = $configuration->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::batchEnd(), $batch)) {
            HistoryEventProcessor::processHistoryEvents(new class ($batch) extends HistoryEventCreator {
                private $batch;

                public function __construct(BatchEntity $batch)
                {
                    $this->batch = $batch;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createBatchEndEvent($this->batch);
                }
            });
        }
    }

    protected function configureQuery(HistoricBatchQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricBatchQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function findCleanableHistoricBatchesReportByCriteria(CleanableHistoricBatchReportImpl $query, Page $page, array $batchOperationsForHistoryCleanup): array
    {
        $query->setCurrentTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $query->setParameter($batchOperationsForHistoryCleanup);
        $query->addOrderingProperty(new QueryOrderingProperty(new QueryPropertyImpl("TYPE_"), Direction::ascending()));
        if (empty($batchOperationsForHistoryCleanup)) {
            return $this->getDbEntityManager()->selectList("selectOnlyFinishedBatchesReportEntities", $query, $page);
        } else {
            return $this->getDbEntityManager()->selectList("selectFinishedBatchesReportEntities", $query, $page);
        }
    }

    public function findCleanableHistoricBatchesReportCountByCriteria(CleanableHistoricBatchReportImpl $query, array $batchOperationsForHistoryCleanup): int
    {
        $query->setCurrentTimestamp(ClockUtil::getCurrentTime()->format('c'));
        $query->setParameter($batchOperationsForHistoryCleanup);
        if (empty($batchOperationsForHistoryCleanup)) {
            return $this->getDbEntityManager()->selectOne("selectOnlyFinishedBatchesReportEntitiesCount", $query);
        } else {
            return $this->getDbEntityManager()->selectOne("selectFinishedBatchesReportEntitiesCount", $query);
        }
    }

    public function deleteHistoricBatchesByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricBatchEntity::class,
                "deleteHistoricBatchesByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    public function addRemovalTimeById(string $id, string $removalTime): void
    {
        $commandContext = Context::getCommandContext();

        $commandContext->getHistoricIncidentManager()
            ->addRemovalTimeToHistoricIncidentsByBatchId($id, $removalTime);

        $commandContext->getHistoricJobLogManager()
            ->addRemovalTimeToJobLogByBatchId($id, $removalTime);

        $parameters = [];
        $parameters["id"] = $id;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricBatchEntity::class, "updateHistoricBatchRemovalTimeById", $parameters);
    }
}
