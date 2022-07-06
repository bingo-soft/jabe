<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\{
    HistoricVariableInstanceInterface,
    HistoricVariableInstanceQueryInterface
};
use Jabe\Engine\Impl\{
    HistoricVariableInstanceQueryImpl,
    Page
};
use Jabe\Engine\Impl\Db\ListQueryParameterObject;
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\Persistence\AbstractHistoricManager;

class HistoricVariableInstanceManager extends AbstractHistoricManager
{
    public function deleteHistoricVariableInstanceByVariableInstanceId(string $historicVariableInstanceId): void
    {
        if ($this->isHistoryEnabled()) {
            $historicVariableInstance = $this->findHistoricVariableInstanceByVariableInstanceId($historicVariableInstanceId);
            if ($historicVariableInstance !== null) {
                $historicVariableInstance->delete();
            }
        }
    }

    public function deleteHistoricVariableInstanceByProcessInstanceIds(array $historicProcessInstanceIds): void
    {
        $parameters = [];
        $parameters["processInstanceIds"] = $historicProcessInstanceIds;
        $this->deleteHistoricVariableInstances($parameters);
    }

    public function deleteHistoricVariableInstancesByTaskProcessInstanceIds(array $historicProcessInstanceIds): void
    {
        $parameters = [];
        $parameters["taskProcessInstanceIds"] = $historicProcessInstanceIds;
        $this->deleteHistoricVariableInstances($parameters);
    }

    /*public function deleteHistoricVariableInstanceByCaseInstanceId(string $historicCaseInstanceId) {
        $this->deleteHistoricVariableInstancesByProcessCaseInstanceId(null, $historicCaseInstanceId);
    }

    public function deleteHistoricVariableInstancesByCaseInstanceIds(array $historicCaseInstanceIds): void
    {
        $parameters = [];
        $parameters["caseInstanceIds"] = $historicCaseInstanceIds;
        $this->deleteHistoricVariableInstances($parameters);
    }*/

    protected function deleteHistoricVariableInstances(array $parameters): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(ByteArrayEntity::class, "deleteHistoricVariableInstanceByteArraysByIds", $parameters);
        $this->getDbEntityManager()->deletePreserveOrder(HistoricVariableInstanceEntity::class, "deleteHistoricVariableInstanceByIds", $parameters);
    }

    /*protected function deleteHistoricVariableInstancesByProcessCaseInstanceId(string $historicProcessInstanceId, string $historicCaseInstanceId): void
    {
        ensureOnlyOneNotNull("Only the process instance or case instance id should be set", historicProcessInstanceId, historicCaseInstanceId);
        if ($this->isHistoryEnabled()) {

            // delete entries in DB
            List<HistoricVariableInstance> historicVariableInstances;
            if ($historicProcessInstanceId !== null) {
             historicVariableInstances = $this->findHistoricVariableInstancesByProcessInstanceId($historicProcessInstanceId);
            } else {
                historicVariableInstances = $this->findHistoricVariableInstancesByCaseInstanceId($historicCaseInstanceId);
            }

            for (HistoricVariableInstance historicVariableInstance : historicVariableInstances) {
                ((HistoricVariableInstanceEntity) historicVariableInstance).delete();
            }

            // delete entries in Cache
            List <HistoricVariableInstanceEntity> cachedHistoricVariableInstances = getDbEntityManager().getCachedEntitiesByType(HistoricVariableInstanceEntity.class);
            for (HistoricVariableInstanceEntity historicVariableInstance : cachedHistoricVariableInstances) {
                // make sure we only delete the right ones (as we cannot make a proper query in the cache)
                if (($historicProcessInstanceId !== null && historicProcessInstanceId.equals($historicVariableInstance.getProcessInstanceId()))
                    || ($historicCaseInstanceId !== null && historicCaseInstanceId.equals($historicVariableInstance.getCaseInstanceId()))) {
                    historicVariableInstance.delete();
                }
            }
        }
    }*/

    public function findHistoricVariableInstancesByProcessInstanceId(string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectHistoricVariablesByProcessInstanceId", $processInstanceId);
    }

    /*public List<HistoricVariableInstance> findHistoricVariableInstancesByCaseInstanceId(string $caseInstanceId) {
        return getDbEntityManager().selectList("selectHistoricVariablesByCaseInstanceId", caseInstanceId);
    }*/

    public function findHistoricVariableInstanceCountByQueryCriteria(HistoricVariableInstanceQueryImpl $historicProcessVariableQuery): int
    {
        $this->configureQuery($historicProcessVariableQuery);
        return $this->getDbEntityManager()->selectOne("selectHistoricVariableInstanceCountByQueryCriteria", $historicProcessVariableQuery);
    }

    public function findHistoricVariableInstancesByQueryCriteria(HistoricVariableInstanceQueryImpl $historicProcessVariableQuery, Page $page): array
    {
        $this->configureQuery($historicProcessVariableQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricVariableInstanceByQueryCriteria", $historicProcessVariableQuery, $page);
    }

    public function findHistoricVariableInstanceByVariableInstanceId(string $variableInstanceId): ?HistoricVariableInstanceEntity
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricVariableInstanceByVariableInstanceId", $variableInstanceId);
    }

    public function deleteHistoricVariableInstancesByTaskId(string $taskId): void
    {
        if ($this->isHistoryEnabled()) {
            $historicProcessVariableQuery = (new HistoricVariableInstanceQueryImpl())->taskIdIn($taskId);
            $historicProcessVariables = $historicProcessVariableQuery->list();
            foreach ($historicProcessVariables as $historicProcessVariable) {
                $this->historicProcessVariable->delete();
            }
        }
    }

    public function addRemovalTimeToVariableInstancesByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricVariableInstanceEntity::class, "updateHistoricVariableInstancesByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeToVariableInstancesByProcessInstanceId(string $processInstanceId, string $removalTime): void
    {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricVariableInstanceEntity::class, "updateHistoricVariableInstancesByProcessInstanceId", $parameters);
    }

    public function findHistoricVariableInstancesByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricVariableInstanceByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findHistoricVariableInstanceCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricVariableInstanceCountByNativeQuery", $parameterMap);
    }

    protected function configureQuery(HistoricVariableInstanceQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricVariableInstanceQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function deleteHistoricVariableInstancesByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
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
                HistoricVariableInstanceEntity::class,
                "deleteHistoricVariableInstancesByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}
