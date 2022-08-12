<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\{
    EventSubscriptionQueryImpl,
    Page,
    ProcessEngineLogger
};
use Jabe\Impl\Db\{
    EnginePersistenceLogger,
    ListQueryParameterObject
};
use Jabe\Impl\Event\EventType;
use Jabe\Impl\JobExecutor\ProcessEventJobHandler;
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\EnsureUtil;

class EventSubscriptionManager extends AbstractManager
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    /** keep track of subscriptions created in the current command */
    protected $createdSignalSubscriptions = [];

    public function insert(EventSubscriptionEntity $persistentObject): void
    {
        parent::insert($persistentObject);
        if ($persistentObject->isSubscriptionForEventType(EventType::signal())) {
            $this->createdSignalSubscriptions[] = $persistentObject;
        }
    }

    public function deleteEventSubscription(EventSubscriptionEntity $persistentObject): void
    {
        $this->getDbEntityManager()->delete($persistentObject);
        if ($persistentObject->isSubscriptionForEventType(EventType::signal())) {
            foreach ($this->createdSignalSubscriptions as $key => $obj) {
                if ($obj->equals($persistentObject)) {
                    unset($this->createdSignalSubscriptions[$key]);
                }
            }
        }

        // if the event subscription has been triggered asynchronously but not yet executed
        $asyncJobs = $this->getJobManager()->findJobsByConfiguration(
            ProcessEventJobHandler::TYPE,
            $persistentObject->getId(),
            $persistentObject->getTenantId()
        );
        foreach ($asyncJobs as $asyncJob) {
            $asyncJob->delete();
        }
    }

    public function deleteAndFlushEventSubscription(EventSubscriptionEntity $persistentObject): void
    {
        $this->deleteEventSubscription($persistentObject);
        $this->getDbEntityManager()->flushEntity($persistentObject);
    }

    public function findEventSubscriptionById(string $id): ?EventSubscriptionEntity
    {
        return $this->getDbEntityManager()->selectOne("selectEventSubscription", $id);
    }

    public function findEventSubscriptionCountByQueryCriteria(EventSubscriptionQueryImpl $eventSubscriptionQueryImpl): int
    {
        $this->configureQuery($eventSubscriptionQueryImpl);
        return $this->getDbEntityManager()->selectOne("selectEventSubscriptionCountByQueryCriteria", $eventSubscriptionQueryImpl);
    }

    public function findEventSubscriptionsByQueryCriteria(EventSubscriptionQueryImpl $eventSubscriptionQueryImpl, Page $page): array
    {
        $this->configureQuery($eventSubscriptionQueryImpl);
        return $this->getDbEntityManager()->selectList("selectEventSubscriptionByQueryCriteria", $eventSubscriptionQueryImpl, $page);
    }

    /**
     * Find all signal event subscriptions with the given event name for any tenant.
     *
     * @see #findSignalEventSubscriptionsByEventNameAndTenantId(String, String)
     */
    public function findSignalEventSubscriptionsByEventName(string $eventName): array
    {
        $query = "selectSignalEventSubscriptionsByEventName";
        $eventSubscriptions = $this->getDbEntityManager()->selectList($query, $this->configureParameterizedQuery($eventName));

        // add events created in this command (not visible yet in query)
        foreach ($this->createdSignalSubscriptions as $entity) {
            if ($entity->getEventName() == $eventName) {
                $eventSubscriptions[] = $entity;
            }
        }
        return $eventSubscriptions;
    }

    /**
     * Find all signal event subscriptions with the given event name and tenant.
     */
    public function findSignalEventSubscriptionsByEventNameAndTenantId(string $eventName, ?string $tenantId): array
    {
        $query = "selectSignalEventSubscriptionsByEventNameAndTenantId";

        $parameter = [];
        $parameter["eventName"] = $eventName;
        $parameter["tenantId"] = $tenantId;
        $eventSubscriptions = $this->getDbEntityManager()->selectList($query, $parameter);

        // add events created in this command (not visible yet in query)
        foreach ($this->createdSignalSubscriptions as $entity) {
            if ($entity->getEventName() == $eventName && $this->hasTenantId($entity, $tenantId)) {
                $eventSubscriptions[] = $entity;
            }
        }
        return $eventSubscriptions;
    }

    /**
     * Find all signal event subscriptions with the given event name which belongs to the given tenant or no tenant.
     */
    public function findSignalEventSubscriptionsByEventNameAndTenantIdIncludeWithoutTenantId(string $eventName, ?string $tenantId): array
    {
        $query = "selectSignalEventSubscriptionsByEventNameAndTenantIdIncludeWithoutTenantId";

        $parameter = [];
        $parameter["eventName"] = $eventName;
        $parameter["tenantId"] = $tenantId;
        $eventSubscriptions = $this->getDbEntityManager()->selectList($query, $parameter);

        // add events created in this command (not visible yet in query)
        foreach ($this->createdSignalSubscriptions as $entity) {
            if ($entity->getEventName() == $eventName && ($entity->getTenantId() === null || $this->hasTenantId($entity, $tenantId))) {
                $eventSubscriptions[] = $entity;
            }
        }
        return $eventSubscriptions;
    }

    protected function hasTenantId(EventSubscriptionEntity $entity, ?string $tenantId): bool
    {
        if ($tenantId === null) {
            return $entity->getTenantId() === null;
        } else {
            return $tenantId == $entity->getTenantId();
        }
    }

    public function findSignalEventSubscriptionsByExecution(string $executionId): array
    {
        $query = "selectSignalEventSubscriptionsByExecution";
        $selectList = $this->getDbEntityManager()->selectList($query, $executionId);

        // add events created in this command (not visible yet in query)
        foreach ($this->createdSignalSubscriptions as $entity) {
            if ($entity->getExecutionId() == $executionId) {
                $selectList[] = $entity;
            }
        }
        return $selectList;
    }

    public function findSignalEventSubscriptionsByNameAndExecution(string $name, string $executionId): array
    {
        $query = "selectSignalEventSubscriptionsByNameAndExecution";
        $params = [];
        $params["executionId"] = $executionId;
        $params["eventName"] = $name;
        $selectList = $this->getDbEntityManager()->selectList($query, $params);

        // add events created in this command (not visible yet in query)
        foreach ($this->createdSignalSubscriptions as $entity) {
            if ($entity->getExecutionId() == $executionId && $name == $entity->getEventName()) {
                $selectList[] = $entity;
            }
        }
        return $selectList;
    }

    public function findEventSubscriptionsByExecutionAndType(string $executionId, string $type, bool $lockResult): array
    {
        $query = "selectEventSubscriptionsByExecutionAndType";
        $params = [];
        $params["executionId"] = $executionId;
        $params["eventType"] = $type;
        $params["lockResult"] = $lockResult;
        return $this->getDbEntityManager()->selectList($query, $params);
    }

    public function findEventSubscriptionsByExecution(string $executionId): array
    {
        $query = "selectEventSubscriptionsByExecution";
        return $this->getDbEntityManager()->selectList($query, $executionId);
    }

    public function findEventSubscriptions(string $executionId, string $type, string $activityId): array
    {
        $query = "selectEventSubscriptionsByExecutionTypeAndActivity";
        $params = [];
        $params["executionId"] = $executionId;
        $params["eventType"] = $type;
        $params["activityId"] = $activityId;
        return $this->getDbEntityManager()->selectList($query, $params);
    }

    public function findEventSubscriptionsByConfiguration(string $type, string $configuration): array
    {
        $query = "selectEventSubscriptionsByConfiguration";
        $params = [];
        $params["eventType"] = $type;
        $params["configuration"] = $configuration;
        return $this->getDbEntityManager()->selectList($query, $params);
    }

    public function findEventSubscriptionsByNameAndTenantId(string $type, string $eventName, ?string $tenantId): array
    {
        $query = "selectEventSubscriptionsByNameAndTenantId";
        $params = [];
        $params["eventType"] = $type;
        $params["eventName"] = $eventName;
        $params["tenantId"] = $tenantId;
        return $this->getDbEntityManager()->selectList($query, $params);
    }

    public function findEventSubscriptionsByNameAndExecution(string $type, string $eventName, string $executionId, bool $lockResult): array
    {
        // first check cache in case entity is already loaded
        $cachedExecution = $this->getDbEntityManager()->getCachedEntity(ExecutionEntity::class, $executionId);
        if ($cachedExecution !== null && !$lockResult) {
            $eventSubscriptions = $cachedExecution->getEventSubscriptions();
            $result = [];
            foreach ($eventSubscriptions as $subscription) {
                if ($this->matchesSubscription($subscription, $type, $eventName)) {
                    $result[] = $subscription;
                }
            }
            return $result;
        } else {
            $query = "selectEventSubscriptionsByNameAndExecution";
            $params = [];
            $params["eventType"] = $type;
            $params["eventName"] = $eventName;
            $params["executionId"] = $executionId;
            $params["lockResult"] = $lockResult;
            return $this->getDbEntityManager()->selectList($query, $params);
        }
    }

    public function findEventSubscriptionsByProcessInstanceId(string $processInstanceId): array
    {
        return $this->getDbEntityManager()->selectList("selectEventSubscriptionsByProcessInstanceId", $processInstanceId);
    }

    /**
     * @return array the message start event subscriptions with the given message name (from any tenant)
     *
     * @see #findMessageStartEventSubscriptionByNameAndTenantId(String, String)
     */
    public function findMessageStartEventSubscriptionByName(string $messageName): array
    {
        return $this->getDbEntityManager()->selectList("selectMessageStartEventSubscriptionByName", $this->configureParameterizedQuery($messageName));
    }

    /**
     * @return EventSubscriptionEntity the message start event subscription with the given message name and tenant id
     *
     * @see #findMessageStartEventSubscriptionByName(String)
     */
    public function findMessageStartEventSubscriptionByNameAndTenantId(string $messageName, string $tenantId): ?EventSubscriptionEntity
    {
        $parameters = [];
        $parameters["messageName"] = $messageName;
        $parameters["tenantId"] = $tenantId;

        return $this->getDbEntityManager()->selectOne("selectMessageStartEventSubscriptionByNameAndTenantId", $parameters);
    }

    /**
     * @param tenantId
     * @return array the conditional start event subscriptions with the given tenant id
     *
     */
    public function findConditionalStartEventSubscriptionByTenantId(string $tenantId): array
    {
        $parameters = [];
        $parameters["tenantId"] = $tenantId;

        $this->configureParameterizedQuery($parameters);
        return $this->getDbEntityManager()->selectList("selectConditionalStartEventSubscriptionByTenantId", $parameters);
    }

    /**
     * @return array the conditional start event subscriptions (from any tenant)
     *
     */
    public function findConditionalStartEventSubscription(): array
    {
        $parameter = new ListQueryParameterObject();

        $this->configurParameterObject($parameter);
        return $this->getDbEntityManager()->selectList("selectConditionalStartEventSubscription", $parameter);
    }

    protected function configurParameterObject(ListQueryParameterObject $parameter): void
    {
        $this->getAuthorizationManager()->configureConditionalEventSubscriptionQuery($parameter);
        $this->getTenantManager()->configureQuery($parameter);
    }

    protected function configureQuery(EventSubscriptionQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureEventSubscriptionQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }

    protected function configureParameterizedQuery(&$parameter): ListQueryParameterObject
    {
        return $this->getTenantManager()->configureQuery($parameter);
    }

    protected function matchesSubscription(EventSubscriptionEntity $subscription, string $type, string $eventName): bool
    {
        EnsureUtil::ensureNotNull("event type", "type", $type);
        $subscriptionEventName = $subscription->getEventName();

        return $type == $subscription->getEventType() &&
                (($eventName === null && $subscriptionEventName === null) || ($eventName !== null && $eventName == $subscriptionEventName));
    }
}
