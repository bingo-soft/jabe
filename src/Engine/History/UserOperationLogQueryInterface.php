<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Query\QueryInterface;

interface UserOperationLogQueryInterface extends QueryInterface
{
    /**
     * Query for operations on entities of a given type only. This allows you to restrict the
     * result set to all operations which were performed on the same Entity (ie. all Task Operations,
     * All IdentityLink Operations ...)
     *
     * @see EntityTypes#TASK
     * @see EntityTypes#IDENTITY_LINK
     * @see EntityTypes#ATTACHMENT
     */
    public function entityType($entityType): UserOperationLogQueryInterface;

    /**
     * Query for operations of a given type only. Types of operations depend on the entity on which the operation
     * was performed. For Instance: Tasks may be delegated, claimed, completed ...
     * Check the {@link UserOperationLogEntry} class for a list of constants of supported operations.
     */
    public function operationType(string $operationType): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given deployment id. */
    public function deploymentId(string $deploymentId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given process definition id. */
    public function processDefinitionId(string $processDefinitionId): UserOperationLogQueryInterface;

    /** Query entries which are operate on all process definitions of the given key. */
    public function processDefinitionKey(string $processDefinitionKey): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given process instance. */
    public function processInstanceId(string $processInstanceId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given execution. */
    public function executionId(string $executionId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given case definition id. */
    public function caseDefinitionId(string $caseDefinitionId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given case instance. */
    public function caseInstanceId(string $caseInstanceId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the given case execution. */
    public function caseExecutionId(string $caseExecutionId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the task. */
    public function taskId(string $taskId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the job. */
    public function jobId(string $jobId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the job definition. */
    public function jobDefinitionId(string $jobDefinitionId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the batch. */
    public function batchId(string $batchId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the user. */
    public function userId(string $userId): UserOperationLogQueryInterface;

    /** Query entries of a composite operation.
     * This allows grouping multiple updates which are part of the same operation:
     * for instance, a User may update multiple fields of a UserTask when calling {@link TaskService#saveTask
     * which will be logged as separate {@link UserOperationLogEntry OperationLogEntries} with the same 'operationId'
     * */
    public function operationId(string $operationId): UserOperationLogQueryInterface;

    /** Query entries which are existing for the external task. */
    public function externalTaskId(string $externalTaskId): UserOperationLogQueryInterface;

    /** Query entries that changed a property. */
    public function property(string $property): UserOperationLogQueryInterface;

    /**
     * Query for operations of the given category only. This allows you to restrict the
     * result set to all operations which were performed in the same domain (ie. all Task Worker Operations,
     * All Admin Operations ...)
     *
     * @see UserOperationLogEntry#CATEGORY_ADMIN
     * @see UserOperationLogEntry#CATEGORY_OPERATOR
     * @see UserOperationLogEntry#CATEGORY_TASK_WORKER
     */
    public function category(string $category): UserOperationLogQueryInterface;

    /**
     * Query for operations of given categories only. This allows you to restrict the
     * result set to all operations which were performed in the same domain (ie. all Task Worker Operations,
     * All Admin Operations ...)
     *
     * @see UserOperationLogEntry#CATEGORY_ADMIN
     * @see UserOperationLogEntry#CATEGORY_OPERATOR
     * @see UserOperationLogEntry#CATEGORY_TASK_WORKER
     */
    public function categoryIn(array $categories): UserOperationLogQueryInterface;

    /** Query entries after the time stamp. */
    public function afterTimestamp(string $after): UserOperationLogQueryInterface;

    /** Query entries before the time stamp. */
    public function beforeTimestamp(string $before): UserOperationLogQueryInterface;

    /** Order by time stamp (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTimestamp(): UserOperationLogQueryInterface;
}
