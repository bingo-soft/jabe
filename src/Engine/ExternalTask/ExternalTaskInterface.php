<?php

namespace BpmPlatform\Engine\ExternalTask;

interface ExternalTaskInterface
{
    /**
     * @return the id of the task
     */
    public function getId(): string;

    /**
     * @return the name of the topic the task belongs to
     */
    public function getTopicName(): string;

    /**
     * @return the id of the worker that has locked the task
     */
    public function getWorkerId(): string;

    /**
     * @return the absolute time at which the lock expires
     */
    public function getLockExpirationTime(): string;

    /**
     * @return the id of the process instance the task exists in
     */
    public function getProcessInstanceId(): string;

    /**
     * @return the id of the execution that the task is assigned to
     */
    public function getExecutionId(): string;

    /**
     * @return the id of the activity for which the task is created
     */
    public function getActivityId(): string;

    /**
     * @return the id of the activity instance in which context the task exists
     */
    public function getActivityInstanceId(): string;

    /**
     * @return the id of the process definition the tasks activity belongs to
     */
    public function getProcessDefinitionId(): string;

    /**
     * @return the key of the process definition the tasks activity belongs to
     */
    public function getProcessDefinitionKey(): string;

    /**
     * @return the version tag of the process definition the tasks activity belongs to
     */
    public function getProcessDefinitionVersionTag(): string;

    /**
     * @return the number of retries left. The number of retries is provided by
     *   a task client, therefore the initial value is <code>null</code>.
     */
    public function getRetries(): int;

    /**
     * @return short error message submitted with the latest reported failure executing this task;
     *   <code>null</code> if no failure was reported previously or if no error message
     *   was submitted
     *
     * @see ExternalTaskService#handleFailure(String, String,String, String, int, long)
     *
     * To get the full error details,
     * use {@link ExternalTaskService#getExternalTaskErrorDetails(String)}
     */
    public function getErrorMessage(): string;

    /**
     * @return bool - true if the external task is suspended; a suspended external task
     * cannot be completed, thereby preventing process continuation
     */
    public function isSuspended(): bool;

    /**
     * @return the id of the tenant the task belongs to. Can be <code>null</code>
     * if the task belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Returns the priority of the external task.
     *
     * @return the priority of the external task
     */
    public function getPriority(): int;

    /**
     * Returns a map containing all custom extension properties of the external task.
     *
     * @return the properties, never <code>null</code>
     */
    public function getExtensionProperties(): array;

    /**
     * Returns the business key of the process instance the external task belongs to
     *
     * @return the business key
     */
    public function getBusinessKey(): ?string;
}
