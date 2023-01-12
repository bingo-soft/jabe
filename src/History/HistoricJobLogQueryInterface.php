<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricJobLogQueryInterface extends QueryInterface
{
    /** Only select historic job log entries with the id. */
    public function logId(?string $logId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the given job id. */
    public function jobId(?string $jobId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the given exception message. */
    public function jobExceptionMessage(?string $exceptionMessage): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the given job definition id. */
    public function jobDefinitionId(?string $jobDefinitionId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the given job definition type. */
    public function jobDefinitionType(?string $jobDefinitionType): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the given job definition configuration type. */
    public function jobDefinitionConfiguration(?string $jobDefinitionConfiguration): HistoricJobLogQueryInterface;

    /** Only select historic job log entries which are associated with one of the given activity ids. **/
    public function activityIdIn(array $activityIds): HistoricJobLogQueryInterface;

    /** Only select historic job log entries which are associated with failures of one of the given activity ids. **/
    public function failedActivityIdIn(array $activityIds): HistoricJobLogQueryInterface;

    /** Only select historic job log entries which are associated with one of the given execution ids. **/
    public function executionIdIn(array $executionIds): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the process instance id. */
    public function processInstanceId(?string $processInstanceId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the process definition id. */
    public function processDefinitionId(?string $processDefinitionId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the process instance key. */
    public function processDefinitionKey(?string $processDefinitionKey): HistoricJobLogQueryInterface;

    /** Only select historic job log entries with the deployment id. */
    public function deploymentId(?string $deploymentId): HistoricJobLogQueryInterface;

    /** Only select historic job log entries that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricJobLogQueryInterface;

    /** Only selects historic job log entries that have no tenant id. */
    public function withoutTenantId(): HistoricJobLogQueryInterface;

    /** Only selects historic job log entries that belong to the given host name. */
    public function hostname(?string $hostname): HistoricJobLogQueryInterface;

    /**
     * Only select log entries where the job had a priority higher than or
     * equal to the given priority.
     */
    public function jobPriorityHigherThanOrEquals(int $priority): HistoricJobLogQueryInterface;

    /**
     * Only select log entries where the job had a priority lower than or
     * equal to the given priority.
     */
    public function jobPriorityLowerThanOrEquals(int $priority): HistoricJobLogQueryInterface;

    /** Only select created historic job log entries. */
    public function creationLog(): HistoricJobLogQueryInterface;

    /** Only select failed historic job log entries. */
    public function failureLog(): HistoricJobLogQueryInterface;

    /**
     * Only select historic job logs which belongs to a
     * <code>successful</code> executed job.
     */
    public function successLog(): HistoricJobLogQueryInterface;

    /** Only select deleted historic job log entries. */
    public function deletionLog(): HistoricJobLogQueryInterface;

    /** Order by timestamp (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTimestamp(): HistoricJobLogQueryInterface;

    /** Order by job id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobId(): HistoricJobLogQueryInterface;

    /** Order by job due date (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobDueDate(): HistoricJobLogQueryInterface;

    /** Order by job retries (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobRetries(): HistoricJobLogQueryInterface;

    /**
     * Order by job priority (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByJobPriority(): HistoricJobLogQueryInterface;

    /** Order by job definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByJobDefinitionId(): HistoricJobLogQueryInterface;

    /** Order by activity id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): HistoricJobLogQueryInterface;

    /** Order by execution id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): HistoricJobLogQueryInterface;

    /** Order by process instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricJobLogQueryInterface;

    /** Order by process definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricJobLogQueryInterface;

    /** Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): HistoricJobLogQueryInterface;

    /** Order by deployment id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeploymentId(): HistoricJobLogQueryInterface;


    /**
     * <p>Sort the {@link HistoricJobLog historic job logs} in the order in which
     * they occurred and needs to be followed by {@link #asc()} or {@link #desc()}.</p>
     *
     * <p>The set of all {@link HistoricJobLog historic job logs} is a <strong>partially ordered
     * set</strong>. Due to this fact {@link HistoricJobLog historic job logs} with different
     * {@link HistoricJobLog#getJobId() job ids} are <strong>incomparable</strong>. Only {@link
     * HistoricJobLog historic job logs} with the same {@link HistoricJobLog#getJobId() job id} can
     * be <strong>totally ordered</strong> by using {@link #jobId(String)} and {@link #orderPartiallyByOccurrence()}
     * which will return a result set ordered by its occurrence.</p>
     */
    public function orderPartiallyByOccurrence(): HistoricJobLogQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of job log entries without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricJobLogQueryInterface;

    /**
     * Order by hostname (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of job log entries without hostname is database-specific.
     */
    public function orderByHostname(): HistoricJobLogQueryInterface;
}
