<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\Resources;
use Jabe\History\{
    CleanableHistoricProcessInstanceReportResultInterface,
    HistoricProcessInstanceInterface
};
use Jabe\Impl\{
    CleanableHistoricProcessInstanceReportImpl,
    HistoricProcessInstanceQueryImpl,
    Page
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityInterface,
    ListQueryParameterObject
};
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\History\Event\HistoricProcessInstanceEventEntity;
use Jabe\Impl\Persistence\AbstractHistoricManager;
use Jabe\Impl\Util\{
    ClockUtil,
    CollectionUtil//,
    //ImmutablePair
};

class HistoricProcessInstanceManager extends AbstractHistoricManager
{
    public function findHistoricProcessInstance(string $processInstanceId): ?HistoricProcessInstanceEntity
    {
        if ($this->isHistoryEnabled()) {
            return $this->getDbEntityManager()->selectById(HistoricProcessInstanceEntity::class, $processInstanceId);
        }
        return null;
    }

    public function findHistoricProcessInstanceEvent(string $eventId): ?HistoricProcessInstanceEventEntity
    {
        if ($this->isHistoryEnabled()) {
            return $this->getDbEntityManager()->selectById(HistoricProcessInstanceEventEntity::class, $eventId);
        }
        return null;
    }

    public function deleteHistoricProcessInstanceByProcessDefinitionId(string $processDefinitionId): void
    {
        if ($this->isHistoryEnabled()) {
            $historicProcessInstanceIds = $this->getDbEntityManager()
            ->selectList("selectHistoricProcessInstanceIdsByProcessDefinitionId", $processDefinitionId);

            if (!empty($historicProcessInstanceIds)) {
                $this->deleteHistoricProcessInstanceByIds($historicProcessInstanceIds);
            }
        }
    }

    public function deleteHistoricProcessInstanceByIds(array $processInstanceIds): void
    {
        if ($this->isHistoryEnabled()) {
            $commandContext = Context::getCommandContext();

            // break down parameter list to not hit query parameter limitations
            $partitions = CollectionUtil::partition($processInstanceIds, DbSqlSessionFactory::MAXIMUM_NUMBER_PARAMS);
            foreach ($partitions as $partition) {
                $commandContext->getHistoricDetailManager()->deleteHistoricDetailsByProcessInstanceIds($partition);
                $commandContext->getHistoricVariableInstanceManager()->deleteHistoricVariableInstanceByProcessInstanceIds($partition);
                $commandContext->getCommentManager()->deleteCommentsByProcessInstanceIds($partition);
                $commandContext->getAttachmentManager()->deleteAttachmentsByProcessInstanceIds($partition);
                $commandContext->getHistoricTaskInstanceManager()->deleteHistoricTaskInstancesByProcessInstanceIds($partition, false);
                $commandContext->getHistoricActivityInstanceManager()->deleteHistoricActivityInstancesByProcessInstanceIds($partition);
                $commandContext->getHistoricIncidentManager()->deleteHistoricIncidentsByProcessInstanceIds($partition);
                $commandContext->getHistoricJobLogManager()->deleteHistoricJobLogsByProcessInstanceIds($partition);
                $commandContext->getHistoricExternalTaskLogManager()->deleteHistoricExternalTaskLogsByProcessInstanceIds($partition);
                $commandContext->getAuthorizationManager()->deleteAuthorizationsByResourceIds(Resources::historicProcessInstance(), $partition);

                $commandContext->getDbEntityManager()->deletePreserveOrder(HistoricProcessInstanceEntity::class, "deleteHistoricProcessInstances", $partition);
            }
        }
    }

    public function findHistoricProcessInstanceCountByQueryCriteria(HistoricProcessInstanceQueryImpl $historicProcessInstanceQuery): int
    {
        if ($this->isHistoryEnabled()) {
            $this->configureQuery($historicProcessInstanceQuery);
            return $this->getDbEntityManager()->selectOne("selectHistoricProcessInstanceCountByQueryCriteria", $historicProcessInstanceQuery);
        }
        return 0;
    }

    public function findHistoricProcessInstancesByQueryCriteria(HistoricProcessInstanceQueryImpl $historicProcessInstanceQuery, Page $page): array
    {
        if ($this->isHistoryEnabled()) {
            $this->configureQuery($historicProcessInstanceQuery);
            return $this->getDbEntityManager()->selectList("selectHistoricProcessInstancesByQueryCriteria", $historicProcessInstanceQuery, $page);
        }
        return [];
    }

    public function findHistoricProcessInstancesByNativeQuery(array $parameterMap, int $firstResult, int $maxResults): array
    {
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricProcessInstanceByNativeQuery", $parameterMap, $firstResult, $maxResults);
    }

    public function findHistoricProcessInstanceCountByNativeQuery(array $parameterMap): int
    {
        return $this->getDbEntityManager()->selectOne("selectHistoricProcessInstanceCountByNativeQuery", $parameterMap);
    }

    protected function configureQuery(HistoricProcessInstanceQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureHistoricProcessInstanceQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    public function findHistoricProcessInstanceIdsForCleanup(int $batchSize, int $minuteFrom, int $minuteTo): array
    {
        $parameters = [];
        $parameters["currentTimestamp"] = ClockUtil::getCurrentTime()->format('c');
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameterObject = new ListQueryParameterObject($parameters, 0, $batchSize);
        return $this->getDbEntityManager()->selectList("selectHistoricProcessInstanceIdsForCleanup", $parameterObject);
    }

    public function findHistoricProcessInstanceIds(HistoricProcessInstanceQueryImpl $historicProcessInstanceQuery): array
    {
        $this->configureQuery($historicProcessInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricProcessInstanceIdsByQueryCriteria", $historicProcessInstanceQuery);
    }

    public function findDeploymentIdMappingsByQueryCriteria(HistoricProcessInstanceQueryImpl $historicProcessInstanceQuery): array
    {
        $this->configureQuery($historicProcessInstanceQuery);
        return $this->getDbEntityManager()->selectList("selectHistoricProcessInstanceDeploymentIdMappingsByQueryCriteria", $historicProcessInstanceQuery);
    }

    public function findCleanableHistoricProcessInstancesReportByCriteria(CleanableHistoricProcessInstanceReportImpl $query, Page $page): array
    {
        $query->setCurrentTimestamp(ClockUtil::getCurrentTime());

        $this->getAuthorizationManager()->configureQueryHistoricFinishedInstanceReport($query, Resources::processDefinition());
        $this->getTenantManager()->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectFinishedProcessInstancesReportEntities", $query, $page);
    }

    public function findCleanableHistoricProcessInstancesReportCountByCriteria(CleanableHistoricProcessInstanceReportImpl $query): int
    {
        $query->setCurrentTimestamp(ClockUtil::getCurrentTime()->format('c'));

        $this->getAuthorizationManager()->configureQueryHistoricFinishedInstanceReport($query, Resources::processDefinition());
        $this->getTenantManager()->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectFinishedProcessInstancesReportEntitiesCount", $query);
    }

    public function addRemovalTimeToProcessInstancesByRootProcessInstanceId(string $rootProcessInstanceId, string $removalTime): void
    {
        $commandContext = Context::getCommandContext();

        $commandContext->getHistoricActivityInstanceManager()
            ->addRemovalTimeToActivityInstancesByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricTaskInstanceManager()
            ->addRemovalTimeToTaskInstancesByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricVariableInstanceManager()
            ->addRemovalTimeToVariableInstancesByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricDetailManager()
            ->addRemovalTimeToDetailsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricIncidentManager()
            ->addRemovalTimeToIncidentsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricExternalTaskLogManager()
            ->addRemovalTimeToExternalTaskLogByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricJobLogManager()
            ->addRemovalTimeToJobLogByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getOperationLogManager()
            ->addRemovalTimeToUserOperationLogByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getHistoricIdentityLinkManager()
            ->addRemovalTimeToIdentityLinkLogByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getCommentManager()
            ->addRemovalTimeToCommentsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getAttachmentManager()
            ->addRemovalTimeToAttachmentsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        $commandContext->getByteArrayManager()
            ->addRemovalTimeToByteArraysByRootProcessInstanceId($rootProcessInstanceId, $removalTime);

        if ($this->isEnableHistoricInstancePermissions()) {
            $commandContext->getAuthorizationManager()
                ->addRemovalTimeToAuthorizationsByRootProcessInstanceId($rootProcessInstanceId, $removalTime);
        }

        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricProcessInstanceEventEntity::class, "updateHistoricProcessInstanceEventsByRootProcessInstanceId", $parameters);
    }

    public function addRemovalTimeById(string $processInstanceId, string $removalTime): void
    {
        $commandContext = Context::getCommandContext();

        $commandContext->getHistoricActivityInstanceManager()
            ->addRemovalTimeToActivityInstancesByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricTaskInstanceManager()
            ->addRemovalTimeToTaskInstancesByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricVariableInstanceManager()
            ->addRemovalTimeToVariableInstancesByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricDetailManager()
            ->addRemovalTimeToDetailsByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricIncidentManager()
            ->addRemovalTimeToIncidentsByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricExternalTaskLogManager()
            ->addRemovalTimeToExternalTaskLogByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricJobLogManager()
            ->addRemovalTimeToJobLogByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getOperationLogManager()
            ->addRemovalTimeToUserOperationLogByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getHistoricIdentityLinkManager()
            ->addRemovalTimeToIdentityLinkLogByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getCommentManager()
            ->addRemovalTimeToCommentsByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getAttachmentManager()
            ->addRemovalTimeToAttachmentsByProcessInstanceId($processInstanceId, $removalTime);

        $commandContext->getByteArrayManager()
            ->addRemovalTimeToByteArraysByProcessInstanceId($processInstanceId, $removalTime);

        if ($this->isEnableHistoricInstancePermissions()) {
            $commandContext->getAuthorizationManager()
                ->addRemovalTimeToAuthorizationsByProcessInstanceId($processInstanceId, $removalTime);
        }

        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(HistoricProcessInstanceEventEntity::class, "updateHistoricProcessInstanceByProcessInstanceId", $parameters);
    }

    public function deleteHistoricProcessInstancesByRemovalTime(string $removalTime, int $minuteFrom, int $minuteTo, int $batchSize): array
    {
        $commandContext = Context::getCommandContext();

        $deleteOperations = [];

        $deleteActivityInstances = $commandContext->getHistoricActivityInstanceManager()
            ->deleteHistoricActivityInstancesByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteActivityInstances->getEntityType()] = $deleteActivityInstances;

        $deleteTaskInstances = $commandContext->getHistoricTaskInstanceManager()
            ->deleteHistoricTaskInstancesByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteTaskInstances->getEntityType()] = $deleteTaskInstances;

        $deleteVariableInstances = $commandContext->getHistoricVariableInstanceManager()
            ->deleteHistoricVariableInstancesByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteVariableInstances->getEntityType()] = $deleteVariableInstances;

        $deleteDetails = $commandContext->getHistoricDetailManager()
            ->deleteHistoricDetailsByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteDetails->getEntityType()] = $deleteDetails;

        $deleteIncidents = $commandContext->getHistoricIncidentManager()
            ->deleteHistoricIncidentsByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteIncidents->getEntityType()] = $deleteIncidents;

        $deleteTaskLog = $commandContext->getHistoricExternalTaskLogManager()
            ->deleteExternalTaskLogByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteTaskLog->getEntityType()] = $deleteTaskLog;

        $deleteJobLog = $commandContext->getHistoricJobLogManager()
            ->deleteJobLogByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteJobLog->getEntityType()] = $deleteJobLog;

        $deleteOperationLog = $commandContext->getOperationLogManager()
            ->deleteOperationLogByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteOperationLog->getEntityType()] = $deleteOperationLog;

        $deleteIdentityLinkLog = $commandContext->getHistoricIdentityLinkManager()
            ->deleteHistoricIdentityLinkLogByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteIdentityLinkLog->getEntityType()] = $deleteIdentityLinkLog;

        $deleteComments = $commandContext->getCommentManager()
            ->deleteCommentsByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteComments->getEntityType()] = $deleteComments;

        $deleteAttachments = $commandContext->getAttachmentManager()
            ->deleteAttachmentsByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteAttachments->getEntityType()] = $deleteAttachments;

        $deleteByteArrays = $commandContext->getByteArrayManager()
            ->deleteByteArraysByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteByteArrays->getEntityType()] = $deleteByteArrays;

        $deleteAuthorizations = $commandContext->getAuthorizationManager()
            ->deleteAuthorizationsByRemovalTime($removalTime, $minuteFrom, $minuteTo, $batchSize);

        $deleteOperations[$deleteAuthorizations->getEntityType()] = $deleteAuthorizations;

        $parameters = [];
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        $deleteProcessInstances = $this->getDbEntityManager()
            ->deletePreserveOrder(
                HistoricProcessInstanceEntity::class,
                "deleteHistoricProcessInstancesByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );

        $deleteOperations[$deleteProcessInstances->getEntityType()] = $deleteProcessInstances;

        return $deleteOperations;
    }

    protected function isEnableHistoricInstancePermissions(): bool
    {
        return Context::getProcessEngineConfiguration()
            ->isEnableHistoricInstancePermissions();
    }
}
