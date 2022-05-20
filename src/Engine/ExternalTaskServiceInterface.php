<?php

namespace Jabe\Engine;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\ExternalTask\{
    ExternalTaskInterface,
    ExternalTaskQueryInterface,
    ExternalTaskQueryBuilderInterface,
    UpdateExternalTaskRetriesBuilderInterface,
    UpdateExternalTaskRetriesSelectBuilderInterface
};

interface ExternalTaskServiceInterface
{
    /**
     * <p>Defines fetching of external tasks by using a fluent builder.
     * The following parameters must be specified:
     * A worker id, a maximum number of tasks to fetch and a flag that indicates
     * whether priority should be regarded or not.
     * The builder allows to specify multiple topics to fetch tasks for and
     * individual lock durations. For every topic, variables can be fetched
     * in addition. If priority is enabled, the tasks with the highest priority are fetched.</p>
     *
     * <p>Returned tasks are locked for the given worker until
     * <code>now + lockDuration</code> expires.
     * Locked tasks cannot be fetched or completed by other workers. When the lock time has expired,
     * a task may be fetched and locked by other workers.</p>
     *
     * <p>Returns at most <code>maxTasks</code> tasks. The tasks are arbitrarily
     * distributed among the specified topics. Example: Fetching 10 tasks of topics
     * "a"/"b"/"c" may return 3/3/4 tasks, or 10/0/0 tasks, etc.</p>
     *
     * <p>May return less than <code>maxTasks</code> tasks, if there exist not enough
     * unlocked tasks matching the provided topics or if parallel fetching by other workers
     * results in locking failures.</p>
     *
     * <p>
     *   Returns only tasks that the currently authenticated user has at least one
     *   permission out of all of the following groups for:
     *
     *   <ul>
     *     <li>{@link Permissions#READ} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#READ_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     * </p>
     *
     * @param maxTasks the maximum number of tasks to return
     * @param workerId the id of the worker to lock the tasks for
     * @param usePriority the flag to enable the priority fetching mechanism
     * @return a builder to define and execute an external task fetching operation
     */
    public function fetchAndLock(int $maxTasks, string $workerId, bool $usePriority = false): ExternalTaskQueryBuilderInterface;

    /**
     * <p>Lock an external task on behalf of a worker.
     *    Note: Attempting to lock an already locked external task with the same <code>workerId</code>
     *    will succeed and a new lock duration will be set, starting from the current moment.
     * </p>
     *
     * @param externalTaskId the id of the external task to lock
     * @param workerId  the id of the worker to lock the task for
     * @param lockDuration the duration in milliseconds for which task should be locked
     * @throws NotFoundException if no external task with the given id exists
     * @throws BadUserRequestException if the task was already locked by a different worker
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function lock(string $externalTaskId, string $workerId, int $lockDuration): void;

    /**
     * <p>Completes an external task on behalf of a worker and submits variables
     * to the process instance before continuing execution. The given task must be
     * assigned to the worker.</p>
     *
     * @param externalTaskId the id of the external task to complete
     * @param workerId the id of the worker that completes the task
     * @param variables a map of variables to set on the execution (non-local)
     *   the external task is assigned to
     *
     * @throws NotFoundException if no external task with the given id exists
     * @throws BadUserRequestException if the task is assigned to a different worker
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function complete(string $externalTaskId, string $workerId, array $variables = [], array $localVariables = []): void;

    /**
     * <p>Extends a lock of an external task on behalf of a worker.
     * The given task must be assigned to the worker.</p>
     *
     * @param externalTaskId the id of the external task
     * @param workerId the id of the worker that extends the lock of the task
     *
     * @throws NotFoundException if no external task with the given id exists
     * @throws BadUserRequestException if the task is assigned to a different worker
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function extendLock(string $externalTaskId, string $workerId, int $newLockDuration): void;

    /**
     * <p>Signals that an external task could not be successfully executed.
     * The task must be assigned to the given worker. The number of retries left can be specified. In addition, a timeout can be
     * provided, such that the task cannot be fetched before <code>now + retryTimeout</code> again.</p>
     *
     * <p>If <code>retries</code> is 0, an incident with the given error message is created. The incident gets resolved,
     * once the number of retries is increased again.</p>
     *
     * <p>Exceptions raised in evaluating expressions of error event definitions attached to the task will be ignored by this method
     * and the event definitions considered as not-matching.</p>
     *
     * @param externalTaskId the id of the external task to report a failure for
     * @param workerId the id of the worker that reports the failure
     * @param errorMessage short error message related to this failure. This message can be retrieved via
     *   {@link ExternalTask#getErrorMessage()} and is used as the incident message in case <code>retries</code> is <code>null</code>.
     *   May be <code>null</code>.
     * @param retries the number of retries left. External tasks with 0 retries cannot be fetched anymore unless
     *   the number of retries is increased via API. Must be >= 0.
     * @param retryTimeout the timeout before the task can be fetched again. Must be >= 0.
     *
     * @throws NotFoundException if no external task with the given id exists
     * @throws BadUserRequestException if the task is assigned to a different worker
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function handleFailure(string $externalTaskId, string $workerId, string $errorMessage, string $errorDetails, int $retries, int $retryDuration, array $variables = [], array $localVariables = []): void;

    /**
     * <p>Signals that an business error appears, which should be handled by the process engine.
     * The task must be assigned to the given worker. The error will be propagated to the next error handler.
     * Is no existing error handler for the given bpmn error the activity instance of the external task
     * ends.</p>
     *
     * @param externalTaskId the id of the external task to report a bpmn error
     * @param workerId the id of the worker that reports the bpmn error
     * @param errorCode the error code of the corresponding bmpn error
     *
     * @throws NotFoundException if no external task with the given id exists
     * @throws BadUserRequestException if the task is assigned to a different worker
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function handleBpmnError(string $externalTaskId, string $workerId, string $errorCode, string $errorMessage = null, array $variables = []): void;

    /**
     * Unlocks an external task instance.
     *
     * @param externalTaskId the id of the task to unlock
     * @throws NotFoundException if no external task with the given id exists
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function unlock(string $externalTaskId): void;

    /**
     * Sets the retries for an external task. If the new value is 0, a new incident with a <code>null</code>
     * message is created. If the old value is 0 and the new value is greater than 0, an existing incident
     * is resolved.
     *
     * @param externalTaskId the id of the task to set the
     * @param retries
     * @throws NotFoundException if no external task with the given id exists
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function setRetries($externalTaskIdOrIds, int $retries, bool $writeUserOperationLog = true): void;

    /**
     * Sets the retries for external tasks asynchronously as batch. The returned batch
     * can be used to track the progress. If the new value is 0, a new incident with a <code>null</code>
     * message is created. If the old value is 0 and the new value is greater than 0, an existing incident
     * is resolved.
     *
     *
     * @return the batch
     *
     * @param externalTaskIds the ids of the tasks to set the
     * @param retries
     * @param externalTaskQuery a query which selects the external tasks to set the retries for.
     * @throws NotFoundException if no external task with one of the given id exists
     * @throws BadUserRequestException if the ids are null or the number of retries is negative
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#CREATE} or
     *          {@link BatchPermissions#CREATE_BATCH_SET_EXTERNAL_TASK_RETRIES} permission on {@link Resources#BATCH}.
     */
    public function setRetriesAsync(array $externalTaskIds, ExternalTaskQueryInterface $externalTaskQuery, int $retries): BatchInterface;

    /**
     * Sets the retries for external tasks using a fluent builder.
     *
     * Specify the instances by calling one of the following methods, like
     * <i>externalTaskIds</i>. To set the retries call
     * {@link UpdateExternalTaskRetriesBuilder#set(int)} or
     * {@link UpdateExternalTaskRetriesBuilder#setAsync(int)}.
     */
    public function updateRetries(): UpdateExternalTaskRetriesSelectBuilderInterface;

    /**
     * Sets the priority for an external task.
     *
     * @param externalTaskId the id of the task to set the
     * @param priority the new priority of the task
     * @throws NotFoundException if no external task with the given id exists
     * @throws AuthorizationException thrown if the current user does not possess any of the following permissions:
     *   <ul>
     *     <li>{@link Permissions#UPDATE} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#UPDATE_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     */
    public function setPriority(string $externalTaskId, int $priority): void;

    /**
     * <p>
     *   Queries for tasks that the currently authenticated user has at least one
     *   of the following permissions for:
     *
     *   <ul>
     *     <li>{@link Permissions#READ} on {@link Resources#PROCESS_INSTANCE}</li>
     *     <li>{@link Permissions#READ_INSTANCE} on {@link Resources#PROCESS_DEFINITION}</li>
     *   </ul>
     * </p>
     *
     * @return a new {@link ExternalTaskQuery} that can be used to dynamically
     * query for external tasks.
     */
    public function createExternalTaskQuery(): ExternalTaskQueryInterface;

    /**
     * Returns a list of distinct topic names of all currently existing external tasks
     * restricted by the parameters.
     * Returns an empty list if no matching tasks are found.
     * Parameters are conjunctive, i.e. only tasks are returned that match all parameters
     * with value <code>true</code>. Parameters with value <code>false</code> are effectively ignored.
     * For example, this means that an empty list is returned if both <code>withLockedTasks</code>
     * and <code>withUnlockedTasks</code> are true.
     *
     * @param withLockedTasks return only topic names of unlocked tasks
     * @param withUnlockedTasks return only topic names of locked tasks
     * @param withRetriesLeft return only topic names of tasks with retries remaining
     */
    public function getTopicNames(bool $withLockedTasks = false, bool $withUnlockedTasks = false, bool $withRetriesLeft = false): array;

    /**
     * Returns the full error details that occurred while running external task
     * with the given id. Returns null when the external task has no error details.
     *
     * @param externalTaskId id of the external task, cannot be null.
     *
     * @throws ProcessEngineException
     *          When no external task exists with the given id.
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ} permission on {@link Resources#PROCESS_INSTANCE}
     *          or no {@link Permissions#READ_INSTANCE} permission on {@link Resources#PROCESS_DEFINITION}.
     */
    public function getExternalTaskErrorDetails(string $externalTaskId): string;
}
