<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricActivityInstanceInterface;
use Jabe\Engine\Impl\{
    HistoricActivityInstanceQueryImpl,
    Page
};
use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\History\Event\HistoricActivityInstanceEventEntity;
use Jabe\Engine\Impl\Persistence\AbstractHistoricManager;

class HistoricActivityInstanceManager extends AbstractHistoricManager
{
    public function deleteHistoricActivityInstancesByProcessInstanceIds(array $historicProcessInstanceIds): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(
            HistoricActivityInstanceEntity::class,
            "deleteHistoricActivityInstancesByProcessInstanceIds",
            $historicProcessInstanceIds
        );
    }

    public function insertHistoricActivityInstance(HistoricActivityInstanceEntity $historicActivityInstance): void
    {
        $this->getDbEntityManager()->insert($historicActivityInstance);
    }

    public function findHistoricActivityInstance(string $activityId, string $processInstanceId): ?HistoricActivityInstanceEntity
    {
        $parameters = [];
        $parameters["activityId"] = $activityId;
        $parameters["processInstanceId"] = $processInstanceId;

        return $this->getDbEntityManager()->selectOne("selectHistoricActivityInstance", $parameters);
    }

    public function findHistoricActivityInstanceCountByQueryCriteria(HistoricActivityInstanceQueryImpl $historicActivityInstanceQuery): int
    {
        $this->configureQuery($historicActivityInstanceQuery);
        return $this->getDbEntityManager()->selectOne("selectHistoricActivityInstanceCountByQueryCriteria", $historicActivityInstanceQuery);
    }

    public function findHistoricActivityInstancesByQueryCriteria(HistoricActivityInstanceQueryImpl $historicActivityInstanceQuery, Page $page): array
    {
        $this->configureQuery($historicActivityInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricActivityInstancesByQueryCriteria", $historicActivityInstanceQuery, $page);
    }

    public function findHistoricActivityInstancesByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricActivityInstanceByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findHistoricActivityInstanceCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricActivityInstanceCountByNativeQuery", $parameterMap);
    }

    protected function configureQuery(HistoricActivityInstanceQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricActivityInstanceQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function addRemovalTimeToActivityInstancesByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricActivityInstanceEventEntity::class, "updateHistoricActivityInstancesByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToActivityInstancesByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricActivityInstanceEventEntity::class, "updateHistoricActivityInstancesByProcessInstanceId", $parameters);
    }

    public function deleteHistoricActivityInstancesByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricActivityInstanceEntity::class,
                "deleteHistoricActivityInstancesByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}
