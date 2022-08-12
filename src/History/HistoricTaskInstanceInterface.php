<?php

namespace Jabe\History;

interface HistoricTaskInstanceInterface
{
    /**
     * The unique identifier of this historic task instance. This is the same identifier as the
     * runtime Task instance.
     */
    public function getId(): string;

    /** Process definition key reference. */
    public function getProcessDefinitionKey(): string;

    /** Process definition reference. */
    public function getProcessDefinitionId(): string;

    /** Root process instance reference. */
    public function getRootProcessInstanceId(): string;

    /** Process instance reference. */
    public function getProcessInstanceId(): string;

    /** Execution reference. */
    public function getExecutionId(): string;

    /** Activity instance reference. */
    public function getActivityInstanceId(): string;

    /** The latest name given to this task. */
    public function getName(): string;

    /** The latest description given to this task. */
    public function getDescription(): ?string;

    /** The reason why this task was deleted {'completed' | 'deleted' | any other user defined string }. */
    public function getDeleteReason(): string;

    /** Task owner */
    public function getOwner(): string;

    /** The latest assignee given to this task. */
    public function getAssignee(): string;

    /** Time when the task started. */
    public function getStartTime(): string;

    /** Time when the task was deleted or completed. */
    public function getEndTime(): string;

    /** Difference between {@link #getEndTime()} and {@link #getStartTime()} in milliseconds.  */
    public function getDurationInMillis(): int;

    /** Task definition key. */
    public function getTaskDefinitionKey(): string;

    /** Task priority **/
    public function getPriority(): string;

    /** Task due date **/
    public function getDueDate(): string;

    /** The parent task of this task, in case this task was a subtask */
    public function getParentTaskId(): ?string;

    /** Task follow-up date */
    public function getFollowUpDate(): string;

    /**
     * The id of the tenant this historic task instance belongs to. Can be <code>null</code>
     * if the historic task instance belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /** The time the historic task instance will be removed. */
    public function getRemovalTime(): string;
}
