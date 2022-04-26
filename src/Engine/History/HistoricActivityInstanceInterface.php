<?php

namespace Jabe\Engine\History;

interface HistoricActivityInstanceInterface
{
    /** The unique identifier of this historic activity instance. */
    public function getId(): string;

    /** return the id of the parent activity instance */
    public function getParentActivityInstanceId(): ?string;

    /** The unique identifier of the activity in the process */
    public function getActivityId(): string;

    /** The display name for the activity */
    public function getActivityName(): string;

    /**
     * The activity type of the activity.
     * Typically the activity type correspond to the XML tag used in the BPMN 2.0 process definition file.
     */
    public function getActivityType(): string;

    /** Process definition key reference */
    public function getProcessDefinitionKey(): string;

    /** Process definition reference */
    public function getProcessDefinitionId(): string;

    /** Root process instance reference */
    public function getRootProcessInstanceId(): string;

    /** Process instance reference */
    public function getProcessInstanceId(): string;

    /** Execution reference */
    public function getExecutionId(): string;

    /** The corresponding task in case of task activity */
    public function getTaskId(): string;

    /** The called process instance in case of call activity */
    public function getCalledProcessInstanceId(): string;

    /** The called case instance in case of (case) call activity */
    public function getCalledCaseInstanceId(): string;

    /** Assignee in case of user task activity */
    public function getAssignee(): string;

    /** Time when the activity instance started */
    public function getStartTime(): string;

    /** Time when the activity instance ended */
    public function getEndTime(): string;

    /** Difference between {@link #getEndTime()} and {@link #getStartTime()}.  */
    public function getDurationInMillis(): int;

    /** Did this activity instance complete a BPMN 2.0 scope */
    public function isCompleteScope(): bool;

    /** Was this activity instance canceled */
    public function isCanceled(): bool;

    /**
     * The id of the tenant this historic activity instance belongs to. Can be <code>null</code>
     * if the historic activity instance belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /** The time the historic activity instance will be removed. */
    public function getRemovalTime(): string;
}
