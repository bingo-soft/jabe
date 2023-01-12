<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    HistoricIncidentQueryImpl,
    Page
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\History\Event\{
    HistoricIncidentEventEntity,
    HistoryEventTypes
};
use Jabe\Impl\Persistence\AbstractHistoricManager;

class HistoricIncidentManager extends AbstractHistoricManager
{
    public function findHistoricIncidentCountByQueryCriteria(HistoricIncidentQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricIncidentCountByQueryCriteria", $query);
    }

    public function findHistoricIncidentById(?string $id): ?HistoricIncidentEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricIncidentById", $id);
    }

    public function findHistoricIncidentByQueryCriteria(HistoricIncidentQueryImpl $query, ?Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricIncidentByQueryCriteria", $query, $page);
    }

    public function addRemovalTimeToIncidentsByRootProcessInstanceId(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIncidentEventEntity::class, "updateHistoricIncidentsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToIncidentsByProcessInstanceId(?string $processInstanceId, ?string $removalTime): void
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

    public function deleteHistoricIncidentsByProcessDefinitionId(?string $processDefinitionId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIncidentEntity::class, "deleteHistoricIncidentsByProcessDefinitionId", $processDefinitionId);
        }
    }

    public function deleteHistoricIncidentsByJobDefinitionId(?string $jobDefinitionId): void
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

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
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

    public function deleteHistoricIncidentsByRemovalTime(?string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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

    public function addRemovalTimeToHistoricIncidentsByBatchId(?string $batchId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["batchId"] = $batchId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIncidentEntity::class, "updateHistoricIncidentsByBatchId", $parameters);
    }
}
