<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricDetailInterface;
use Jabe\Engine\Impl\{
    HistoricDetailQueryImpl,
    Page
};
use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\History\Event\HistoricDetailEventEntity;
use Jabe\Engine\Impl\Persistence\AbstractHistoricManager;

class HistoricDetailManager extends AbstractHistoricManager
{
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

    public function deleteHistoricDetailsByVariableInstanceId(string $historicVariableInstanceId): void
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

    public function findHistoricDetailsByQueryCriteria(HistoricDetailQueryImpl $historicVariableUpdateQuery, Page $page): array
    {
        $this->configureQuery($historicVariableUpdateQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricDetailsByQueryCriteria", $historicVariableUpdateQuery, $page);
    }

    public function deleteHistoricDetailsByTaskId(string $taskId): void
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

    public function findHistoricDetailsByTaskId(string $taskId): array
    {
        return $this->getDbEntityManager()->selectList("selectHistoricDetailsByTaskId", $taskId);
    }

    protected function configureQuery(HistoricDetailQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricDetailQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function addRemovalTimeToDetailsByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricDetailEventEntity::class, "updateHistoricDetailsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToDetailsByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricDetailEventEntity::class, "updateHistoricDetailsByProcessInstanceId", $parameters);
    }

    public function deleteHistoricDetailsByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
