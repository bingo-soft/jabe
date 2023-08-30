<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    HistoricIdentityLinkLogQueryImpl,
    Page
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\History\Event\{
    HistoricIdentityLinkLogEventEntity,
    HistoryEventTypes
};
use Jabe\Impl\Persistence\AbstractHistoricManager;

class HistoricIdentityLinkLogManager extends AbstractHistoricManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function findHistoricIdentityLinkLogCountByQueryCriteria(HistoricIdentityLinkLogQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricIdentityLinkCountByQueryCriteria", $query);
    }

    public function findHistoricIdentityLinkLogByQueryCriteria(HistoricIdentityLinkLogQueryImpl $query, ?Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricIdentityLinkByQueryCriteria", $query, $page);
    }

    public function addRemovalTimeToIdentityLinkLogByRootProcessInstanceId(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIdentityLinkLogEventEntity::class, "updateIdentityLinkLogByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToIdentityLinkLogByProcessInstanceId(?string $processInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIdentityLinkLogEventEntity::class, "updateIdentityLinkLogByProcessInstanceId", $parameters);
    }

    public function deleteHistoricIdentityLinksLogByProcessDefinitionId(?string $processDefId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIdentityLinkLogEntity::class, "deleteHistoricIdentityLinksByProcessDefinitionId", $processDefId);
        }
    }

    public function deleteHistoricIdentityLinksLogByTaskId(?string $taskId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIdentityLinkLogEntity::class, "deleteHistoricIdentityLinksByTaskId", $taskId);
        }
    }

    public function deleteHistoricIdentityLinksLogByTaskProcessInstanceIds(array $processInstanceIds): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(HistoricIdentityLinkLogEntity::class, "deleteHistoricIdentityLinksByTaskProcessInstanceIds", $processInstanceIds);
    }

    /*public function deleteHistoricIdentityLinksLogByTaskCaseInstanceIds(List<String> caseInstanceIds) {
        getDbEntityManager().deletePreserveOrder(HistoricIdentityLinkLogEntity.class, "deleteHistoricIdentityLinksByTaskCaseInstanceIds", caseInstanceIds);
    }*/

    public function deleteHistoricIdentityLinkLogByRemovalTime(?string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricIdentityLinkLogEntity::class,
                "deleteHistoricIdentityLinkLogByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureHistoricIdentityLinkQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function isHistoryEventProduced(): bool
    {
        $historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        return $historyLevel->isHistoryEventProduced(HistoryEventTypes::identityLinkAdd(), null) ||
               $historyLevel->isHistoryEventProduced(HistoryEventTypes::identityLinkDelete(), null);
    }
}
