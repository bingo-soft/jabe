<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\ProcessEngineException;
use Jabe\Impl\{
    Page,
    ProcessDefinitionQueryImpl,
    ProcessEngineLogger,
    ProcessInstanceQueryImpl
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    EnginePersistenceLogger,
    ListQueryParameterObject
};
use Jabe\Impl\Event\EventType;
use Jabe\Impl\JobExecutor\TimerStartEventJobHandler;
use Jabe\Impl\Persistence\{
    AbstractManager,
    AbstractResourceDefinitionManagerInterface
};
use Jabe\Repository\ProcessDefinitionInterface;

class ProcessDefinitionManager extends AbstractManager implements AbstractResourceDefinitionManagerInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    // insert ///////////////////////////////////////////////////////////

    public function insertProcessDefinition(ProcessDefinitionEntity $processDefinition): void
    {
        $this->getDbEntityManager()->insert($processDefinition);
        $this->createDefaultAuthorizations($processDefinition);
    }

    // select ///////////////////////////////////////////////////////////

    /**
     * @return ProcessDefinitionEntity the latest version of the process definition with the given key (from any tenant)
     *
     * @throws ProcessEngineException if more than one tenant has a process definition with the given key
     *
     * @see #findLatestProcessDefinitionByKeyAndTenantId(String, String)
     */
    public function findLatestProcessDefinitionByKey(string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        $processDefinitions = $this->findLatestProcessDefinitionsByKey($processDefinitionKey);

        if (empty($processDefinitions)) {
            return null;
        } elseif (count($processDefinitions) == 1) {
            return $processDefinitions[0];
        } else {
            //throw LOG.multipleTenantsForProcessDefinitionKeyException($processDefinitionKey);
            throw new \Exception("findLatestProcessDefinitionByKey");
        }
    }

    /**
     * @return array the latest versions of the process definition with the given key (from any tenant),
     *         contains multiple elements if more than one tenant has a process definition with
     *         the given key
     *
     * @see #findLatestProcessDefinitionByKey(String)
     */
    public function findLatestProcessDefinitionsByKey(string $processDefinitionKey): array
    {
        return $this->getDbEntityManager()->selectList("selectLatestProcessDefinitionByKey", $this->configureParameterizedQuery($processDefinitionKey));
    }

    /**
     * @return ProcessDefinitionEntity the latest version of the process definition with the given key and tenant id
     *
     * @see #findLatestProcessDefinitionByKeyAndTenantId(String, String)
     */
    public function findLatestProcessDefinitionByKeyAndTenantId(string $processDefinitionKey, ?string $tenantId): ?ProcessDefinitionEntity
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["tenantId"] = $tenantId;

        if ($tenantId === null) {
            return $this->getDbEntityManager()->selectOne("selectLatestProcessDefinitionByKeyWithoutTenantId", $parameters);
        } else {
            return $this->getDbEntityManager()->selectOne("selectLatestProcessDefinitionByKeyAndTenantId", $parameters);
        }
    }

    public function findLatestProcessDefinitionById(string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $this->getDbEntityManager()->selectById(ProcessDefinitionEntity::class, $processDefinitionId);
    }

    public function findProcessDefinitionsByQueryCriteria(ProcessDefinitionQueryImpl $processDefinitionQuery, Page $page): array
    {
        $this->configureProcessDefinitionQuery($processDefinitionQuery);
        return $this->getDbEntityManager()->selectList("selectProcessDefinitionsByQueryCriteria", $processDefinitionQuery, $page);
    }

    public function findProcessDefinitionCountByQueryCriteria(ProcessDefinitionQueryImpl $processDefinitionQuery): int
    {
        $this->configureProcessDefinitionQuery($processDefinitionQuery);
        return $this->getDbEntityManager()->selectOne("selectProcessDefinitionCountByQueryCriteria", $processDefinitionQuery);
    }

    public function findProcessDefinitionByDeploymentAndKey(string $deploymentId, string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        $parameters = [];
        $parameters["deploymentId"] = $deploymentId;
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        return $this->getDbEntityManager()->selectOne("selectProcessDefinitionByDeploymentAndKey", $parameters);
    }

    public function findProcessDefinitionByKeyVersionAndTenantId(string $processDefinitionKey, int $processDefinitionVersion, ?string $tenantId): ?ProcessDefinitionEntity
    {
        return $this->findProcessDefinitionByKeyVersionOrVersionTag($processDefinitionKey, $processDefinitionVersion, null, $tenantId);
    }

    public function findProcessDefinitionByKeyVersionTagAndTenantId(string $processDefinitionKey, string $processDefinitionVersionTag, ?string $tenantId): ?ProcessDefinitionEntity
    {
        return $this->findProcessDefinitionByKeyVersionOrVersionTag($processDefinitionKey, null, $processDefinitionVersionTag, $tenantId);
    }

    protected function findProcessDefinitionByKeyVersionOrVersionTag(
        string $processDefinitionKey,
        int $processDefinitionVersion,
        string $processDefinitionVersionTag,
        ?string $tenantId
    ): ?ProcessDefinitionEntity {
        $parameters = [];
        if ($processDefinitionVersion !== null) {
            $parameters["processDefinitionVersion"] = $processDefinitionVersion;
        } elseif ($processDefinitionVersionTag !== null) {
            $parameters["processDefinitionVersionTag"] = $processDefinitionVersionTag;
        }
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["tenantId"] = $tenantId;

        $results = $this->getDbEntityManager()->selectList("selectProcessDefinitionByKeyVersionAndTenantId", $parameters);
        if (count($results) == 1) {
            return $results[0];
        } elseif (count($results) > 1) {
            if ($processDefinitionVersion !== null) {
                //throw LOG.toManyProcessDefinitionsException(results.size(), $processDefinitionKey, "version", $processDefinitionVersion.toString(), $tenantId);
                throw new \Exception("version");
            } elseif ($processDefinitionVersionTag !== null) {
                //throw LOG.toManyProcessDefinitionsException(results.size(), $processDefinitionKey, "versionTag", $processDefinitionVersionTag, $tenantId);
                throw new \Exception("versionTag");
            }
        }
        return null;
    }

    public function findProcessDefinitionsByKey(string $processDefinitionKey): array
    {
        $processDefinitionQuery = (new ProcessDefinitionQueryImpl())
          ->processDefinitionKey($processDefinitionKey);
        return $this->findProcessDefinitionsByQueryCriteria($processDefinitionQuery, null);
    }

    public function findProcessDefinitionsStartableByUser(dtring $user): array
    {
        return (new ProcessDefinitionQueryImpl())->startableByUser($user)->list();
    }

    public function findPreviousProcessDefinitionId(string $processDefinitionKey, int $version, ?string $tenantId): string
    {
        $params = [];
        $params["key"] = $processDefinitionKey;
        $params["version"] = $version;
        $params["tenantId"] = $tenantId;
        return $this->getDbEntityManager()->selectOne("selectPreviousProcessDefinitionId", $params);
    }

    public function findProcessDefinitionsByDeploymentId(string $deploymentId): array
    {
        return $this->getDbEntityManager()->selectList("selectProcessDefinitionByDeploymentId", $deploymentId);
    }

    public function findProcessDefinitionsByKeyIn(array $keys): array
    {
        return $this->getDbEntityManager()->selectList("selectProcessDefinitionByKeyIn", $keys);
    }

    public function findDefinitionsByKeyAndTenantId(string $processDefinitionKey, ?string $tenantId, bool $isTenantIdSet): array
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isTenantIdSet"] = $isTenantIdSet;
        $parameters["tenantId"] = $tenantId;

        return $this->getDbEntityManager()->selectList("selectProcessDefinitions", $parameters);
    }

    public function findDefinitionsByIds(array $processDefinitionIds): array
    {
        $parameters = [];
        $parameters["processDefinitionIds"] = $processDefinitionIds;
        $parameters["isTenantIdSet"] = false;

        return $this->getDbEntityManager()->selectList("selectProcessDefinitions", $parameters);
    }

    // update ///////////////////////////////////////////////////////////

    public function updateProcessDefinitionSuspensionStateById(string $processDefinitionId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionId"] = $processDefinitionId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ProcessDefinitionEntity::class, "updateProcessDefinitionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateProcessDefinitionSuspensionStateByKey(string $processDefinitionKey, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isTenantIdSet"] = false;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ProcessDefinitionEntity::class, "updateProcessDefinitionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    public function updateProcessDefinitionSuspensionStateByKeyAndTenantId(string $processDefinitionKey, ?string $tenantId, SuspensionState $suspensionState): void
    {
        $parameters = [];
        $parameters["processDefinitionKey"] = $processDefinitionKey;
        $parameters["isTenantIdSet"] = true;
        $parameters["tenantId"] = $tenantId;
        $parameters["suspensionState"] = $suspensionState->getStateCode();
        $this->getDbEntityManager()->update(ProcessDefinitionEntity::class, "updateProcessDefinitionSuspensionStateByParameters", $this->configureParameterizedQuery($parameters));
    }

    // delete  ///////////////////////////////////////////////////////////

    /**
     * Cascades the deletion of the process definition to the process instances.
     * Skips the custom listeners if the flag was set to true.
     *
     * @param processDefinitionId the process definition id
     * @param skipCustomListeners true if the custom listeners should be skipped at process instance deletion
     * @param skipIoMappings specifies whether input/output mappings for tasks should be invoked
     */
    protected function cascadeDeleteProcessInstancesForProcessDefinition(string $processDefinitionId, bool $skipCustomListeners, bool $skipIoMappings): void
    {
        $this->getProcessInstanceManager()
            ->deleteProcessInstancesByProcessDefinition($processDefinitionId, "deleted process definition", true, $skipCustomListeners, $skipIoMappings);
    }

    /**
     * Cascades the deletion of a process definition to the history, deletes the history.
     *
     * @param processDefinitionId the process definition id
     */
    protected function cascadeDeleteHistoryForProcessDefinition(string $processDefinitionId): void
    {
        // remove historic incidents which are not referenced to a process instance
        $this->getHistoricIncidentManager()->deleteHistoricIncidentsByProcessDefinitionId($processDefinitionId);

        // remove historic identity links which are not reference to a process instance
        $this->getHistoricIdentityLinkManager()->deleteHistoricIdentityLinksLogByProcessDefinitionId($processDefinitionId);

        // remove historic job log entries not related to a process instance
        $this->getHistoricJobLogManager()->deleteHistoricJobLogsByProcessDefinitionId($processDefinitionId);
    }

    /**
     * Deletes the timer start events for the given process definition.
     *
     * @param processDefinition the process definition
     */
    protected function deleteTimerStartEventsForProcessDefinition(ProcessDefinitionInterface $processDefinition): void
    {
        $timerStartJobs = $this->getJobManager()->findJobsByConfiguration(TimerStartEventJobHandler::TYPE, $processDefinition->getKey(), $processDefinition->getTenantId());

        $latestVersion = $this->getProcessDefinitionManager()
            ->findLatestProcessDefinitionByKeyAndTenantId($processDefinition->getKey(), $processDefinition->getTenantId());

        // delete timer start event jobs only if this is the latest version of the process definition.
        if ($latestVersion !== null && $latestVersion->getId() == $processDefinition->getId()) {
            foreach ($timerStartJobs as $job) {
                $job->delete();
            }
        }
    }

    /**
     * Deletes the subscriptions for the process definition, which is
     * identified by the given process definition id.
     *
     * @param processDefinitionId the id of the process definition
     */
    public function deleteSubscriptionsForProcessDefinition(string $processDefinitionId): void
    {
        $eventSubscriptionsToRemove = [];
        // remove message event subscriptions:
        $messageEventSubscriptions = $this->getEventSubscriptionManager()
          ->findEventSubscriptionsByConfiguration(EventType::message()->name(), $processDefinitionId);
        $eventSubscriptionsToRemove = array_merge($eventSubscriptionsToRemove, $messageEventSubscriptions);

        // remove signal event subscriptions:
        $signalEventSubscriptions = $this->getEventSubscriptionManager()->findEventSubscriptionsByConfiguration(EventType::signal()->name(), $processDefinitionId);
        $eventSubscriptionsToRemove = array_merge($eventSubscriptionsToRemove, $signalEventSubscriptions);

        // remove conditional event subscriptions:
        $conditionalEventSubscriptions = $this->getEventSubscriptionManager()->findEventSubscriptionsByConfiguration(EventType::conditional()->name(), $processDefinitionId);
        $eventSubscriptionsToRemove = array_merge($eventSubscriptionsToRemove, $conditionalEventSubscriptions);

        foreach ($eventSubscriptionsToRemove as $eventSubscriptionEntity) {
            $eventSubscriptionEntity->delete();
        }
    }

   /**
    * Deletes the given process definition from the database and cache.
    * If cascadeToHistory and cascadeToInstances is set to true it deletes
    * the history and the process instances.
    *
    * *Note*: If more than one process definition, from one deployment, is deleted in
    * a single transaction and the cascadeToHistory and cascadeToInstances flag was set to true it
    * can cause a dirty deployment cache. The process instances of ALL process definitions must be deleted,
    * before every process definition can be deleted! In such cases the cascadeToInstances flag
    * have to set to false!
    *
    * On deletion of all process instances, the task listeners will be deleted as well.
    * Deletion of tasks and listeners needs the redeployment of deployments.
    * It can cause to problems if is done sequential with the deletion of process definition
    * in a single transaction.
    *
    * *For example*:
    * Deployment contains two process definition. First process definition
    * and instances will be removed, also cleared from the cache.
    * Second process definition will be removed and his instances.
    * Deletion of instances will cause redeployment this deploys again
    * first into the cache. Only the second will be removed from cache and
    * first remains in the cache after the deletion process.
    *
    * @param processDefinition the process definition which should be deleted
    * @param processDefinitionId the id of the process definition
    * @param cascadeToHistory if true the history will deleted as well
    * @param cascadeToInstances if true the process instances are deleted as well
    * @param skipCustomListeners if true skips the custom listeners on deletion of instances
    * @param skipIoMappings specifies whether input/output mappings for tasks should be invoked
    */
    public function deleteProcessDefinition(ProcessDefinitionInterface $processDefinition, string $processDefinitionId, bool $cascadeToHistory, bool $cascadeToInstances, bool $skipCustomListeners, bool $skipIoMappings): void
    {
        if ($cascadeToHistory) {
            $this->cascadeDeleteHistoryForProcessDefinition($processDefinitionId);
            if ($cascadeToInstances) {
                $this->cascadeDeleteProcessInstancesForProcessDefinition($processDefinitionId, $skipCustomListeners, $skipIoMappings);
            }
        } else {
            $procInstQuery = (new ProcessInstanceQueryImpl())->processDefinitionId($processDefinitionId);
            $processInstanceCount = $this->getProcessInstanceManager()->findProcessInstanceCountByQueryCriteria($procInstQuery);
            if ($processInstanceCount != 0) {
                //throw LOG.deleteProcessDefinitionWithProcessInstancesException($processDefinitionId, $processInstanceCount);
                throw new \Exception("deleteProcessDefinition");
            }
        }

        // remove related authorization parameters in IdentityLink table
        $this->getIdentityLinkManager()->deleteIdentityLinksByProcDef($processDefinitionId);

        // remove timer start events:
        $this->deleteTimerStartEventsForProcessDefinition($processDefinition);

        //delete process definition from database
        $this->getDbEntityManager()->delete(ProcessDefinitionEntity::class, "deleteProcessDefinitionsById", $processDefinitionId);

        // remove process definition from cache:
        Context::getProcessEngineConfiguration()
            ->getDeploymentCache()
            ->removeProcessDefinition($processDefinitionId);

        $this->deleteSubscriptionsForProcessDefinition($processDefinitionId);

        // delete job definitions
        $this->getJobDefinitionManager()->deleteJobDefinitionsByProcessDefinitionId($processDefinition->getId());
    }

    // helper ///////////////////////////////////////////////////////////

    protected function createDefaultAuthorizations(ProcessDefinitionInterface $processDefinition): void
    {
        if ($this->isAuthorizationEnabled()) {
            $provider = $this->getResourceAuthorizationProvider();
            $authorizations = $provider->newProcessDefinition($processDefinition);
            $this->saveDefaultAuthorizations($authorizations);
        }
    }

    protected function configureProcessDefinitionQuery(ProcessDefinitionQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureProcessDefinitionQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery($parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }

    public function findLatestDefinitionByKey(string $key)
    {
        return $this->findLatestProcessDefinitionByKey($key);
    }

    public function findLatestDefinitionById(string $id)
    {
        return $this->findLatestProcessDefinitionById($id);
    }

    public function getCachedResourceDefinitionEntity(string $definitionId)
    {
        return $this->getDbEntityManager()->getCachedEntity(ProcessDefinitionEntity::class, $definitionId);
    }

    public function findLatestDefinitionByKeyAndTenantId(string $definitionKey, ?string $tenantId)
    {
        return $this->findLatestProcessDefinitionByKeyAndTenantId($definitionKey, $tenantId);
    }

    public function findDefinitionByKeyVersionAndTenantId(string $definitionKey, int $definitionVersion, ?string $tenantId)
    {
        return $this->findProcessDefinitionByKeyVersionAndTenantId($definitionKey, $definitionVersion, $tenantId);
    }

    public function findDefinitionByKeyVersionTagAndTenantId(string $definitionKey, string $definitionVersionTag, ?string $tenantId)
    {
        return $this->findProcessDefinitionByKeyVersionTagAndTenantId($definitionKey, $definitionVersionTag, $tenantId);
    }

    public function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey)
    {
        return $this->findProcessDefinitionByDeploymentAndKey($deploymentId, $definitionKey);
    }
}
