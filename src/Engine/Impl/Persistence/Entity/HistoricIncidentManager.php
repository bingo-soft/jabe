<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricIncidentInterface;
use BpmPlatform\Engine\Impl\{
    HistoricIncidentQueryImpl,
    Page
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\ListQueryParameterObject;
use BpmPlatform\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricIncidentEventEntity,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\Persistence\AbstractHistoricManager;

class HistoricIncidentManager extends AbstractHistoricManager
{
    public function findHistoricIncidentCountByQueryCriteria(HistoricIncidentQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricIncidentCountByQueryCriteria", $query);
    }

    public function findHistoricIncidentById(string $id): ?HistoricIncidentEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricIncidentById", $id);
    }

    public function findHistoricIncidentByQueryCriteria(HistoricIncidentQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricIncidentByQueryCriteria", $query, $page);
    }

    public function addRemovalTimeToIncidentsByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIncidentEventEntity::class, "updateHistoricIncidentsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToIncidentsByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIncidentEventEntity::class, "updateHistoricIncidentsByProcessInstanceId", $parameters);
    }

    public function deleteHistoricIncidentsByProcessInstanceIds(array $processInstanceIds): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(HistoricIncidentEntity::class, "deleteHistoricIncidentsByProcessInstanceIds", $processInstanceIds);
    }

    public function deleteHistoricIncidentsByProcessDefinitionId(string $processDefinitionId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIncidentEntity::class, "deleteHistoricIncidentsByProcessDefinitionId", $processDefinitionId);
        }
    }

    public function deleteHistoricIncidentsByJobDefinitionId(string $jobDefinitionId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIncidentEntity::class, "deleteHistoricIncidentsByJobDefinitionId", $jobDefinitionId);
        }
    }

    public function deleteHistoricIncidentsByBatchId(array $historicBatchIds): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIncidentEntity::class, "deleteHistoricIncidentsByBatchIds", $historicBatchIds);
        }
    }

    protected function configureQuery(HistoricIncidentQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricIncidentQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function isHistoryEventProduced(): bool
    {
        $historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        return $historyLevel->isHistoryEventProduced(HistoryEventTypes::incidentCreate(), null) ||
                $historyLevel->isHistoryEventProduced(HistoryEventTypes::incidentDelete(), null) ||
                $historyLevel->isHistoryEventProduced(HistoryEventTypes::incidentMigrate(), null) ||
                $historyLevel->isHistoryEventProduced(HistoryEventTypes::incidentResolve(), null);
    }

    public function deleteHistoricIncidentsByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricIncidentEntity::class,
                "deleteHistoricIncidentsByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    public function addRemovalTimeToHistoricIncidentsByBatchId(string $batchId, string $removalTime): void
    {
        $parameters = [];
        $parameters["batchId"] = $batchId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIncidentEntity::class, "updateHistoricIncidentsByBatchId", $parameters);
    }
}
