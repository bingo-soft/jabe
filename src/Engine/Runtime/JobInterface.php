<?php

namespace BpmPlatform\Engine\Runtime;

interface JobInterface
{
    /**
     * Returns the unique identifier for this job.
     */
    public function getId(): string;

    /**
     * Returns the date on which this job is supposed to be processed.
     */
    public function getDuedate(): string;

    /**
     * Returns the id of the process instance which execution created the job.
     */
    public function getProcessInstanceId(): string;

    /**
     * Returns the id of the process definition which created the job.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Returns the key of the process definition which created the job.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * Returns the specific execution on which the job was created.
     */
    public function getExecutionId(): string;

    /**
     * Returns the number of retries this job has left.
     * Whenever the jobexecutor fails to execute the job, this value is decremented.
     * When it hits zero, the job is supposed to be dead and not retried again
     * (ie a manual retry is required then).
     */
    public function getRetries(): int;

    /**
     * Returns the message of the exception that occurred, the last time the job was
     * executed. Returns null when no exception occurred.
     *
     * To get the full exception stacktrace,
     * use ManagementService#getJobExceptionStacktrace
     */
    public function getExceptionMessage(): string;

    /** Returns the id of the activity on which the last exception occurred. */
    public function getFailedActivityId(): string;

    /**
     * Returns the id of the deployment in which context the job was created.
     */
    public function getDeploymentId(): string;

    /**
     * The id of the JobDefinition for this job.
     */
    public function getJobDefinitionId(): string;

    /**
     * Indicates whether this job is suspended. If a job is suspended,
     * the job will be not acquired by the job executor.
     *
     * @return true if this Job is currently suspended.
     */
    public function isSuspended(): bool;

    /**
     * The job's priority that is a hint to job acquisition.
     */
    public function getPriority(): float;

    /**
     * The id of the tenant this job belongs to. Can be <code>null</code>
     * if the job belongs to no single tenant.
     */
    public function getTenantId(): string;

    /** The date/time when this job has been created */
    public function getCreateTime(): string;
}
