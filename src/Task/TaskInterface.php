<?php

namespace Jabe\Task;

interface TaskInterface
{
    public const PRIORITY_MINIUM = 0;
    public const PRIORITY_NORMAL = 50;
    public const PRIORITY_MAXIMUM = 100;

    /** DB id of the task. */
    public function getId(): ?string;

    /** Name or title of the task. */
    public function getName(): ?string;

    /** Name or title of the task. */
    public function setName(?string $name): void;

    /** Free text description of the task. */
    public function getDescription(): ?string;

    /** Change the description of the task */
    public function setDescription(?string $description);

      /** indication of how important/urgent this task is with a number between
       * 0 and 100 where higher values mean a higher priority and lower values mean
       * lower priority: [0..19] lowest, [20..39] low, [40..59] normal, [60..79] high
       * [80..100] highest */
    public function getPriority(): int;

    /** indication of how important/urgent this task is with a number between
     * 0 and 100 where higher values mean a higher priority and lower values mean
     * lower priority: [0..19] lowest, [20..39] low, [40..59] normal, [60..79] high
     * [80..100] highest */
    public function setPriority(int $priority): void;

    /** The {@link User#getId() userId} of the person that is responsible for this task.
     * This is used when a task is {@link TaskService#delegateTask(String, String) delegated}. */
    public function getOwner(): ?string;

    /** The {@link User#getId() userId} of the person that is responsible for this task.
     * This is used when a task is {@link TaskService#delegateTask(String, String) delegated}. */
    public function setOwner(?string $owner): void;

    /** The {@link User#getId() userId} of the person to which this task is
     * {@link TaskService#setAssignee(String, String) assigned} or
     * {@link TaskService#delegateTask(String, String) delegated}. */
    public function getAssignee(): ?string;

    /** The {@link User#getId() userId} of the person to which this task is
     * {@link TaskService#setAssignee(String, String) assigned} or
     * {@link TaskService#delegateTask(String, String) delegated}. */
    public function setAssignee(?string $assignee): void;

    /** The current DelegationState for this task. */
    public function getDelegationState(): ?string;

    /** The current DelegationState for this task. */
    public function setDelegationState(?string $delegationState): void;

    /** Reference to the process instance or null if it is not related to a process instance. */
    public function getProcessInstanceId(): ?string;

    /** Reference to the path of execution or null if it is not related to a process instance. */
    public function getExecutionId(): ?string;

    /** Reference to the process definition or null if it is not related to a process. */
    public function getProcessDefinitionId(): ?string;

    /** The date/time when this task was created */
    public function getCreateTime(): ?string;

    /** The id of the activity in the process defining this task or null if this is not related to a process */
    public function getTaskDefinitionKey(): ?string;

    /** Due date of the task. */
    public function getDueDate(): ?string;

    /** Change due date of the task. */
    public function setDueDate(?string $dueDate): void;

    /** Follow-up date of the task. */
    public function getFollowUpDate(): ?string;

    /** Change follow-up date of the task. */
    public function setFollowUpDate(?string $followUpDate): void;

    /** delegates this task to the given user and sets the {@link #getDelegationState() delegationState} to DelegationState#PENDING.
      * If no owner is set on the task, the owner is set to the current assignee of the task. */
    public function delegate(?string $userId): void;

    /** the parent task for which this task is a subtask */
    public function setParentTaskId(?string $parentTaskId): void;

    /** the parent task for which this task is a subtask */
    public function getParentTaskId(): ?string;

    /** Indicated whether this task is suspended or not. */
    public function isSuspended(): bool;

    /**
     * Provides the form key for the task.
     *
     * <p><strong>NOTE:</strong> If the task instance us obtained through a query, this property is only populated in case the
     * TaskQuery#initializeFormKeys() method is called. If this method is called without a prior call to
     * TaskQuery#initializeFormKeys(), it will throw a BadUserRequestException.</p>
     *
     * @return string the form key for this task
     * @throws BadUserRequestException in case the form key is not initialized.
     */
    public function getFormKey(): ?string;

    /**
     * Returns the task's tenant id or null in case this task does not belong to a tenant.
     *
     * @return string the task's tenant id or null
     */
    public function getTenantId(): ?string;

    /**
     * Sets the tenant id for this task.
     *
     * @param tenantId the tenant id to set
     */
    public function setTenantId(?string $tenantId): void;
}
