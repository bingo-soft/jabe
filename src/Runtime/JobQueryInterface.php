<?php

namespace Jabe\Runtime;

use Jabe\Query\QueryInterface;

interface JobQueryInterface extends QueryInterface
{
    /** Only select jobs with the given id */
    public function jobId(string $jobId): JobQueryInterface;

    /** Only select jobs whose id is in the given set of ids */
    public function jobIds(array $ids): JobQueryInterface;

    /** Only select jobs which exist for the given job definition id. **/
    public function jobDefinitionId(string $jobDefinitionId): JobQueryInterface;

    /** Only select jobs which exist for the given process instance. **/
    public function processInstanceId(string $processInstanceId): JobQueryInterface;

    /** Only select jobs which exist for any of the given process instance ids */
    public function processInstanceIds(array $processInstanceIds): JobQueryInterface;

    /** Only select jobs which exist for the given process definition id. **/
    public function processDefinitionId(string $processDefinitionId): JobQueryInterface;

    /** Only select jobs which exist for the given process definition key. **/
    public function processDefinitionKey(string $processDefinitionKey): JobQueryInterface;

    /** Only select jobs which exist for the given execution */
    public function executionId(string $executionId): JobQueryInterface;

    /** Only select jobs which are defined on an activity with the given id. **/
    public function activityId(string $activityId): JobQueryInterface;

    /** Only select jobs which have retries left */
    public function withRetriesLeft(): JobQueryInterface;

    /** Only select jobs which are executable,
     * ie. retries &gt; 0 and duestring $is null or duestring $is in the past **/
    public function executable(): JobQueryInterface;

    /** Only select jobs that are timers.
     * Cannot be used together with messages */
    public function timers(): JobQueryInterface;

    /** Only select jobs that are messages.
     * Cannot be used together with timers */
    public function messages(): JobQueryInterface;

    /** Only select jobs where the duestring $is lower than the given date. */
    public function duedateLowerThan(string $date): JobQueryInterface;

    /** Only select jobs where the duestring $is higher then the given date. */
    public function duedateHigherThan(string $date): JobQueryInterface;

    /** Only select jobs created before the given date. */
    public function createdBefore(string $date): JobQueryInterface;

    /** Only select jobs created after the given date. */
    public function createdAfter(string $date): JobQueryInterface;

    /**
     * Only select jobs with a priority that is higher than or equal to the given priority.
     */
    public function priorityHigherThanOrEquals(float $priority): JobQueryInterface;

    /**
     * Only select jobs with a priority that is lower than or equal to the given priority.
     */
    public function priorityLowerThanOrEquals(float $priority): JobQueryInterface;

    /** Only select jobs that failed due to an exception. */
    public function withException(): JobQueryInterface;

    /** Only select jobs that failed due to an exception with the given message. */
    public function exceptionMessage(string $exceptionMessage): JobQueryInterface;

    /** Only select jobs that failed due to an exception at an activity with the given id. **/
    public function failedActivityId(string $activityId): JobQueryInterface;

    /** Only select jobs which have no retries left */
    public function noRetriesLeft(): JobQueryInterface;

    /** Only select jobs that are not suspended. */
    public function active(): JobQueryInterface;

    /** Only select jobs that are suspended. */
    public function suspended(): JobQueryInterface;

    /** Only select jobs that befloat $to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): JobQueryInterface;

    /** Only select jobs which have no tenant id. */
    public function withoutTenantId(): JobQueryInterface;

    /**
     * Select jobs which have no tenant id. Can be used in combination
     * with enantIdIn(array).
     */
    public function includeJobsWithoutTenantId(): JobQueryInterface;

    //sorting //////////////////////////////////////////

    /** Order by job id (needs to be followed by asc or desc). */
    public function orderByJobId(): JobQueryInterface;

    /** Order by duestring $(needs to be followed by asc or desc). */
    public function orderByJobDuedate(): JobQueryInterface;

    /** Order by retries (needs to be followed by asc or desc). */
    public function orderByJobRetries(): JobQueryInterface;

    /**
     * Order by priority for execution (needs to be followed by asc or desc).
     */
    public function orderByJobPriority(): JobQueryInterface;

    /** Order by process instance id (needs to be followed by asc or desc). */
    public function orderByProcessInstanceId(): JobQueryInterface;

    /** Order by process definition id (needs to be followed by asc or desc). */
    public function orderByProcessDefinitionId(): JobQueryInterface;

    /** Order by process definition key (needs to be followed by asc or desc). */
    public function orderByProcessDefinitionKey(): JobQueryInterface;

    /** Order by execution id (needs to be followed by asc or desc). */
    public function orderByExecutionId(): JobQueryInterface;

    /**
     * Order by tenant id (needs to be followed by asc or desc).
     * Note that the ordering of job without tenant id is database-specific.
     */
    public function orderByTenantId(): JobQueryInterface;
}
