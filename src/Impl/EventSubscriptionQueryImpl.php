<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\EventSubscriptionQueryInterface;

class EventSubscriptionQueryImpl extends AbstractQuery implements \Serializable, EventSubscriptionQueryInterface
{
    protected $eventSubscriptionId;
    protected $eventName;
    protected $eventType;
    protected $executionId;
    protected $processInstanceId;
    protected $activityId;

    protected $isTenantIdSet = false;
    protected $tenantIds = [];
    protected $includeEventSubscriptionsWithoutTenantId = false;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function serialize()
    {
        return json_encode([
            'eventSubscriptionId' => $this->eventSubscriptionId,
            'eventName' => $this->eventName,
            'eventType' => $this->eventType,
            'executionId' => $this->executionId,
            'processInstanceId' => $this->processInstanceId,
            'activityId' => $this->activityId,
            'isTenantIdSet' => $this->isTenantIdSet,
            'tenantIds' => $this->tenantIds,
            'includeEventSubscriptionsWithoutTenantId' => $this->includeEventSubscriptionsWithoutTenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->eventSubscriptionId = $json->eventSubscriptionId;
        $this->eventName = $json->eventName;
        $this->eventType = $json->eventType;
        $this->executionId = $json->executionId;
        $this->processInstanceId = $json->processInstanceId;
        $this->activityId = $json->activityId;
        $this->isTenantIdSet = $json->isTenantIdSet;
        $this->tenantIds = $json->tenantIds;
        $this->includeEventSubscriptionsWithoutTenantId = $json->includeEventSubscriptionsWithoutTenantId;
    }

    public function eventSubscriptionId(string $id): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("event subscription id", "id", $id);
        $this->eventSubscriptionId = $id;
        return $this;
    }

    public function eventName(string $eventName): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("event name", "eventName", $eventName);
        $this->eventName = $eventName;
        return $this;
    }

    public function executionId(string $executionId): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("execution id", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function processInstanceId(string $processInstanceId): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("process instance id", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function activityId(string $activityId): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("activity id", "activityId", $activityId);
        $this->activityId = $activityId;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): EventSubscriptionQueryInterface
    {
        $this->isTenantIdSet = true;
        $this->tenantIds = null;
        return $this;
    }

    public function includeEventSubscriptionsWithoutTenantId(): EventSubscriptionQueryInterface
    {
        $this->includeEventSubscriptionsWithoutTenantId  = true;
        return $this;
    }

    public function eventType(string $eventType): EventSubscriptionQueryInterface
    {
        EnsureUtil::ensureNotNull("event type", "eventType", $eventType);
        $this->eventType = $eventType;
        return $this;
    }

    public function orderByCreated(): EventSubscriptionQueryInterface
    {
        return $this->orderBy(EventSubscriptionQueryProperty::created());
    }

    public function orderByTenantId(): EventSubscriptionQueryInterface
    {
        return $this->orderBy(EventSubscriptionQueryProperty::tenantId());
    }

    //results //////////////////////////////////////////

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getEventSubscriptionManager()
            ->findEventSubscriptionCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getEventSubscriptionManager()
            ->findEventSubscriptionsByQueryCriteria($this, $page);
    }

    //getters //////////////////////////////////////////

    public function getEventSubscriptionId(): string
    {
        return $this->eventSubscriptionId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }
}
