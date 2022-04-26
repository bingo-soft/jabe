<?php

namespace Jabe\Engine\ExternalTask;

use Jabe\Engine\Query\QueryInterface;

interface ExternalTaskQueryInterface extends QueryInterface
{
    /**
     * Only select the external task with the given id
     */
    public function externalTaskId(string $externalTaskId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks with any of the given ids
     */
    public function externalTaskIdIn(array $externalTaskIds): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that was most recently locked by the given worker
     */
    public function workerId(string $workerId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that have a lock expiring before the given date
     */
    public function lockExpirationBefore(string $lockExpirationDate): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that have a lock expiring after the given date
     */
    public function lockExpirationAfter(string $lockExpirationDate): ExternalTaskQueryInterface;

    /**
     * Only select external tasks of the given topic
     */
    public function topicName(string $topicName): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that are currently locked, i.e. that have a lock expiration
     * time that is in the future
     */
    public function locked(): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that are not currently locked, i.e. that have no
     * lock expiration time or one that is overdue
     */
    public function notLocked(): ExternalTaskQueryInterface;

    /**
     * Only select external tasks created in the context of the given execution
     */
    public function executionId(string $executionId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks created in the context of the given process instance
     */
    public function processInstanceId(string $processInstanceId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks created in the context of the given process instances
     */
    public function processInstanceIdIn(array $processInstanceIdIn): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that belong to an instance of the given process definition
     */
    public function processDefinitionId(string $processDefinitionId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that belong to an instance of the given activity
     */
    public function activityId(string $activityId): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that belong to an instances of the given activities.
     */
    public function activityIdIn(array $activityIdIn): ExternalTaskQueryInterface;

    /**
     * Only select external tasks with a priority that is higher than or equal to the given priority.
     *
     * @param priority the priority which is used for the query
     * @return the builded external task query
     */
    public function priorityHigherThanOrEquals(int $priority): ExternalTaskQueryInterface;

    /**
     * Only select external tasks with a priority that is lower than or equal to the given priority.
     *
     * @param priority the priority which is used for the query
     * @return the builded external task query
     */
    public function priorityLowerThanOrEquals(int $priority): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that are currently suspended
     */
    public function suspended(): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that are currently not suspended
     */
    public function active(): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that have retries > 0
     */
    public function withRetriesLeft(): ExternalTaskQueryInterface;

    /**
     * Only select external tasks that have retries = 0
     */
    public function noRetriesLeft(): ExternalTaskQueryInterface;

    /** Only select external tasks that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): ExternalTaskQueryInterface;

    /**
     * Order by external task id (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderById(): ExternalTaskQueryInterface;

    /**
     * Order by lock expiration time (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Ordering of tasks with no lock expiration time is database-dependent.
     */
    public function orderByLockExpirationTime(): ExternalTaskQueryInterface;

    /**
     * Order by process instance id (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByProcessInstanceId(): ExternalTaskQueryInterface;

    /**
     * Order by process definition id (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByProcessDefinitionId(): ExternalTaskQueryInterface;

    /**
     * Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByProcessDefinitionKey(): ExternalTaskQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of external tasks without tenant id is database-specific.
     */
    public function orderByTenantId(): ExternalTaskQueryInterface;

    /**
     * Order by priority (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByPriority(): ExternalTaskQueryInterface;
}
