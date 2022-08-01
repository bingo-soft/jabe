<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\{
    HistoricIdentityLinkLogQueryImpl,
    Page
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\History\Event\{
    HistoricIdentityLinkLogEventEntity,
    HistoryEventTypes
};
use Jabe\Engine\Impl\Persistence\AbstractHistoricManager;

class HistoricIdentityLinkLogManager extends AbstractHistoricManager
{
    public function findHistoricIdentityLinkLogCountByQueryCriteria(HistoricIdentityLinkLogQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectHistoricIdentityLinkCountByQueryCriteria", $query);
    }

    public function findHistoricIdentityLinkLogByQueryCriteria(HistoricIdentityLinkLogQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectHistoricIdentityLinkByQueryCriteria", $query, $page);
    }

    public function addRemovalTimeToIdentityLinkLogByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIdentityLinkLogEventEntity::class, "updateIdentityLinkLogByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToIdentityLinkLogByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricIdentityLinkLogEventEntity::class, "updateIdentityLinkLogByProcessInstanceId", $parameters);
    }

    public function deleteHistoricIdentityLinksLogByProcessDefinitionId(string $processDefId): void
    {
        if ($this->isHistoryEventProduced()) {
            $this->getDbEntityManager()->delete(HistoricIdentityLinkLogEntity::class, "deleteHistoricIdentityLinksByProcessDefinitionId", $processDefId);
        }
    }

    public function deleteHistoricIdentityLinksLogByTaskId(string $taskId): void
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

    public function deleteHistoricIdentityLinkLogByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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

    protected function configureQuery(HistoricIdentityLinkLogQueryImpl $query): void
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
