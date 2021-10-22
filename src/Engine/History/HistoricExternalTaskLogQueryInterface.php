<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Query\QueryInterface;

interface HistoricExternalTaskLogQueryInterface extends QueryInterface
{
    /** Only select historic external task log entries with the id. */
    public function logId(string $historicExternalTaskLogId): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the given external task id. */
    public function externalTaskId(string $taskId): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the given topic name. */
    public function topicName(string $topicName): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the given worker id. */
    public function workerId(string $workerId): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the given error message. */
    public function errorMessage(string $errorMessage): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries which are associated with one of the given activity ids. **/
    public function activityIdIn(array $activityIds): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries which are associated with one of the given activity instance ids. **/
    public function activityInstanceIdIn(array $activityInstanceIds): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries which are associated with one of the given execution ids. **/
    public function executionIdIn(array $executionIds): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the process instance id. */
    public function processInstanceId(string $processInstanceId): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the process definition id. */
    public function processDefinitionId(string $processDefinitionId): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries with the process instance key. */
    public function processDefinitionKey(string $processDefinitionKey): HistoricExternalTaskLogQueryInterface;

    /** Only select historic external task log entries that beint $to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricExternalTaskLogQueryInterface;

    /** Only selects historic external task log entries that have no tenant id. */
    public function withoutTenantId(): HistoricExternalTaskLogQueryInterface;

    /**
     * Only select log entries where the external task had a priority higher than or
     * equal to the given priority.
     */
    public function priorityHigherThanOrEquals(int $priority): HistoricExternalTaskLogQueryInterface;

    /**
     * Only select log entries where the external task had a priority lower than or
     * equal to the given priority.
     */
    public function priorityLowerThanOrEquals(int $priority): HistoricExternalTaskLogQueryInterface;

    /** Only select created historic external task log entries. */
    public function creationLog(): HistoricExternalTaskLogQueryInterface;

    /** Only select failed historic external task log entries. */
    public function failureLog(): HistoricExternalTaskLogQueryInterface;

    /** Only select successful historic external task log entries. */
    public function successLog(): HistoricExternalTaskLogQueryInterface;

    /** Only select deleted historic external task log entries. */
    public function deletionLog(): HistoricExternalTaskLogQueryInterface;


    /** Order by timestamp (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTimestamp(): HistoricExternalTaskLogQueryInterface;

    /** Order by external task id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExternalTaskId(): HistoricExternalTaskLogQueryInterface;

    /** Order by external task retries (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByRetries(): HistoricExternalTaskLogQueryInterface;

    /**
     * Order by external task priority (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByPriority(): HistoricExternalTaskLogQueryInterface;

    /** Order by topic name (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTopicName(): HistoricExternalTaskLogQueryInterface;

    /** Order by worker id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByWorkerId(): HistoricExternalTaskLogQueryInterface;

    /** Order by activity id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): HistoricExternalTaskLogQueryInterface;

    /** Order by activity instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityInstanceId(): HistoricExternalTaskLogQueryInterface;

    /** Order by execution id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): HistoricExternalTaskLogQueryInterface;

    /** Order by process instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricExternalTaskLogQueryInterface;

    /** Order by process definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricExternalTaskLogQueryInterface;

    /** Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): HistoricExternalTaskLogQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of external task log entries without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricExternalTaskLogQueryInterface;
}
