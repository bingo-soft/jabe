<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\{
    HistoricDetailQueryImpl,
    Page
};
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\History\Event\HistoricDetailEventEntity;
use Jabe\Impl\Persistence\AbstractHistoricManager;

class HistoricDetailManager extends AbstractHistoricManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function deleteHistoricDetailsByProcessInstanceIds(array $historicProcessInstanceIds): void
    {
        $parameters = [];
        $parameters["processInstanceIds"] = $historicProcessInstanceIds;
        $this->deleteHistoricDetails($parameters);
    }

    public function deleteHistoricDetailsByTaskProcessInstanceIds(array $historicProcessInstanceIds): void
    {
        $parameters = [];
        $parameters["taskProcessInstanceIds"] = $historicProcessInstanceIds;
        $this->deleteHistoricDetails($parameters);
    }

    /*public function deleteHistoricDetailsByCaseInstanceIds(List<String> historicCaseInstanceIds) {
        Map<String, Object> parameters = new HashMap<String, Object>();
        parameters.put("caseInstanceIds", historicCaseInstanceIds);
        deleteHistoricDetails(parameters);
    }

    public void deleteHistoricDetailsByTaskCaseInstanceIds(List<String> historicCaseInstanceIds) {
      Map<String, Object> parameters = new HashMap<String, Object>();
      parameters.put("taskCaseInstanceIds", historicCaseInstanceIds);
      deleteHistoricDetails(parameters);
    }*/

    public function deleteHistoricDetailsByVariableInstanceId(?string $historicVariableInstanceId): void
    {
        $parameters = [];
        $parameters["variableInstanceId"] = $historicVariableInstanceId;
        $this->deleteHistoricDetails($parameters);
    }

    public function deleteHistoricDetails(array $parameters): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(ByteArrayEntity::class, "deleteHistoricDetailByteArraysByIds", $parameters);
        $this->getDbEntityManager()->deletePreserveOrder(HistoricDetailEventEntity::class, "deleteHistoricDetailsByIds", $parameters);
    }

    public function findHistoricDetailCountByQueryCriteria(HistoricDetailQueryImpl $historicVariableUpdateQuery): int
    {
        $this->configureQuery($historicVariableUpdateQuery);
        return $this->getDbEntityManager()->selectOne("selectHistoricDetailCountByQueryCriteria", $historicVariableUpdateQuery);
    }

    public function findHistoricDetailsByQueryCriteria(HistoricDetailQueryImpl $historicVariableUpdateQuery, ?Page $page): array
    {
        $this->configureQuery($historicVariableUpdateQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricDetailsByQueryCriteria", $historicVariableUpdateQuery, $page);
    }

    public function deleteHistoricDetailsByTaskId(?string $taskId): void
    {
        if ($this->isHistoryEnabled()) {
            // delete entries in DB
            $historicDetails = $this->findHistoricDetailsByTaskId($taskId);

            foreach ($historicDetails as $historicDetail) {
                $historicDetail->delete();
            }

            //delete entries in Cache
            $cachedHistoricDetails = $this->getDbEntityManager()->getCachedEntitiesByType(HistoricDetailEventEntity::class);
            foreach ($cachedHistoricDetails as $historicDetail) {
                // make sure we only delete the right ones (as we cannot make a proper query in the cache)
                if ($taskId == $historicDetail->getTaskId()) {
                    $historicDetail->delete();
                }
            }
        }
    }

    public function findHistoricDetailsByTaskId(?string $taskId): array
    {
        return $this->getDbEntityManager()->selectList("selectHistoricDetailsByTaskId", $taskId);
    }

    public function configureQuery($query, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getAuthorizationManager()->configureHistoricDetailQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function addRemovalTimeToDetailsByRootProcessInstanceId(?string $rootProcessInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricDetailEventEntity::class, "updateHistoricDetailsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToDetailsByProcessInstanceId(?string $processInstanceId, ?string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricDetailEventEntity::class, "updateHistoricDetailsByProcessInstanceId", $parameters);
    }

    public function deleteHistoricDetailsByRemovalTime(?string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricDetailEventEntity::class,
                "deleteHistoricDetailsByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}
