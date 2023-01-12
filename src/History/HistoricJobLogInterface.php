<?php

namespace Jabe\History;

interface HistoricJobLogInterface
{
    /**
     * Returns the unique identifier for <code>this</code> historic job log.
     */
    public function getId(): ?string;

    /**
     * Returns the time when <code>this</code> log occurred.
     */
    public function getTimestamp(): ?string;

    /**
     * Returns the id of the associated job.
     */
    public function getJobId(): ?string;

    /**
     * Returns the due date of the associated job when <code>this</code> log occurred.
     */
    public function getJobDueDate(): ?string;

    /**
     * Returns the retries of the associated job before the associated job has
     * been executed and when <code>this</code> log occurred.
     */
    public function getJobRetries(): int;

    /**
     * Returns the priority of the associated job when <code>this</code> log entry was created.
     */
    public function getJobPriority(): int;

    /**
     * Returns the message of the exception that occurred by executing the associated job.
     *
     * To get the full exception stacktrace,
     * use HistoryService#getHistoricJobLogExceptionStacktrace(String)
     */
    public function getJobExceptionMessage(): ?string;

    /**
     * Returns the id of the job definition on which the associated job was created.
     */
    public function getJobDefinitionId(): ?string;

    /**
     * Returns the job definition type of the associated job.
     */
    public function getJobDefinitionType(): ?string;

    /**
     * Returns the job definition configuration type of the associated job.
     */
    public function getJobDefinitionConfiguration(): ?string;

    /**
     * Returns the id of the activity on which the associated job was created.
     */
    public function getActivityId(): ?string;

    /**
     * Returns the id of the activity on which the last exception occurred.
     */
    public function getFailedActivityId(): ?string;

    /**
     * Returns the specific execution id on which the associated job was created.
     */
    public function getExecutionId(): ?string;

    /**
     * Returns the specific root process instance id of the process instance
     * on which the associated job was created.
     */
    public function getRootProcessInstanceId(): ?string;

    /**
     * Returns the specific process instance id on which the associated job was created.
     */
    public function getProcessInstanceId(): ?string;

    /**
     * Returns the specific process definition id on which the associated job was created.
     */
    public function getProcessDefinitionId(): ?string;

    /**
     * Returns the specific process definition key on which the associated job was created.
     */
    public function getProcessDefinitionKey(): ?string;

    /**
     * Returns the specific deployment id on which the associated job was created.
     */
    public function getDeploymentId(): ?string;

    /**
     * Returns the id of the tenant this job log entry belongs to. Can be <code>null</code>
     * if the job log entry belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Returns the name of the host where the Process Engine that added this job log runs.
     */
    public function getHostname(): ?string;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the creation of the associated job.
     */
    public function isCreationLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the failed execution of the associated job.
     */
    public function isFailureLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the successful execution of the associated job.
     */
    public function isSuccessLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the deletion of the associated job.
     */
    public function isDeletionLog(): bool;

    /** The time the historic job log will be removed. */
    public function getRemovalTime(): ?string;
}
