<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Query\QueryInterface;

interface EventSubscriptionQueryInterface extends QueryInterface
{
    /** Only select subscriptions with the given id. **/
    public function eventSubscriptionId(string $id): EventSubscriptionQueryInterface;

    /** Only select subscriptions for events with the given name. **/
    public function eventName(string $eventName): EventSubscriptionQueryInterface;

    /** Only select subscriptions for events with the given type. "message" selects message event subscriptions,
     * "signal" selects signal event subscriptions, "compensation" selects compensation event subscriptions,
     * "conditional" selects conditional event subscriptions.**/
    public function eventType(string $eventType): EventSubscriptionQueryInterface;

    /** Only select subscriptions that belong to an execution with the given id. **/
    public function executionId(string $executionId): EventSubscriptionQueryInterface;

    /** Only select subscriptions that belong to a process instance with the given id. **/
    public function processInstanceId(string $processInstanceId): EventSubscriptionQueryInterface;

    /** Only select subscriptions that belong to an activity with the given id. **/
    public function activityId(string $activityId): EventSubscriptionQueryInterface;

    /** Only select subscriptions that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): EventSubscriptionQueryInterface;

    /** Only select subscriptions which have no tenant id. */
    public function withoutTenantId(): EventSubscriptionQueryInterface;

    /**
     * Select subscriptions which have no tenant id.
     */
    public function includeEventSubscriptionsWithoutTenantId(): EventSubscriptionQueryInterface;

    /** Order by event subscription creation date (needs to be followed by asc or desc). */
    public function orderByCreated(): EventSubscriptionQueryInterface;

    /**
     * Order by tenant id (needs to be followed by asc or desc).
     * Note that the ordering of subscriptions without tenant id is database-specific.
     */
    public function orderByTenantId(): EventSubscriptionQueryInterface;
}
