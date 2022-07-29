<?php

namespace Jabe\Engine\History;

interface HistoricExternalTaskLogInterface
{
    /**
     * Returns the unique identifier for <code>this</code> historic external task log.
     */
    public function getId(): string;

    /**
     * Returns the time when <code>this</code> log occurred.
     */
    public function getTimestamp(): string;

    /**
     * Returns the id of the associated external task.
     */
    public function getExternalTaskId(): string;

    /**
     * Returns the retries of the associated external task before the associated external task has
     * been executed and when <code>this</code> log occurred.
     */
    public function getRetries(): int;

    /**
     * Returns the priority of the associated external task when <code>this</code> log entry was created.
     */
    public function getPriority(): int;

    /**
     * Returns the topic name of the associated external task.
     */
    public function getTopicName(): string;

    /**
     * Returns the id of the worker that fetched the external task most recently.
     */
    public function getWorkerId(): string;

    /**
     * Returns the message of the error that occurred by executing the associated external task.
     *
     * To get the full error details,
     * use HistoryService#getHistoricExternalTaskLogErrorDetails(String)
     */
    public function getErrorMessage(): string;

    /**
     * Returns the id of the activity which the external task associated with.
     */
    public function getActivityId(): string;

    /**
     * Returns the id of the activity instance on which the associated external task was created.
     */
    public function getActivityInstanceId(): string;

    /**
     * Returns the specific execution id on which the associated external task was created.
     */
    public function getExecutionId(): string;

    /**
     * Returns the specific root process instance id of the process instance
     * on which the associated external task was created.
     */
    public function getRootProcessInstanceId(): string;

    /**
     * Returns the specific process instance id on which the associated external task was created.
     */
    public function getProcessInstanceId(): string;

    /**
     * Returns the specific process definition id on which the associated external task was created.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Returns the specific process definition key on which the associated external task was created.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * Returns the id of the tenant this external task log entry belongs to. Can be <code>null</code>
     * if the external task log entry belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the creation of the associated external task.
     */
    public function isCreationLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the failed execution of the associated external task.
     */
    public function isFailureLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the successful execution of the associated external task.
     */
    public function isSuccessLog(): bool;

    /**
     * Returns <code>true</code> when <code>this</code> log represents
     * the deletion of the associated external task.
     */
    public function isDeletionLog(): bool;

    /** The time the historic external task log will be removed. */
    public function getRemovalTime(): string;
}
