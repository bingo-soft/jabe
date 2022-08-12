<?php

namespace Jabe\History;

interface HistoricProcessInstanceInterface
{
    public const STATE_ACTIVE = "ACTIVE";
    public const STATE_SUSPENDED = "SUSPENDED";
    public const STATE_COMPLETED = "COMPLETED";
    public const STATE_EXTERNALLY_TERMINATED = "EXTERNALLY_TERMINATED";
    public const STATE_INTERNALLY_TERMINATED = "INTERNALLY_TERMINATED";

    /** The process instance id (== as the id for the runtime {@link ProcessInstance process instance}). */
    public function getId(): string;

    /** The user provided unique reference to this process instance. */
    public function getBusinessKey(): ?string;

    /** The process definition key reference. */
    public function getProcessDefinitionKey(): string;

    /** The process definition reference. */
    public function getProcessDefinitionId(): string;

    /** The process definition name. */
    public function getProcessDefinitionName(): string;

    /** The process definition version. */
    public function getProcessDefinitionVersion(): int;

    /** The time the process was started. */
    public function getStartTime(): string;

    /** The time the process was ended. */
    public function getEndTime(): string;

    /** The time the historic process instance will be removed. */
    public function getRemovalTime(): string;

    /** The difference between {@link #getEndTime()} and {@link #getStartTime()} . */
    public function getDurationInMillis(): int;

    /** The authenticated user that started this process instance.
     * @see IdentityService#setAuthenticatedUserId(String) */
    public function getStartUserId(): string;

    /** The start activity. */
    public function getStartActivityId(): string;

    /** Obtains the reason for the process instance's deletion. */
    public function getDeleteReason(): string;

    /**
     * The process instance id of a potential super process instance or null if no super process instance exists
     */
    public function getSuperProcessInstanceId(): string;

    /**
     * The process instance id of the top-level (root) process instance or null if no root process instance exists
     */
    public function getRootProcessInstanceId(): string;

    /**
     * The case instance id of a potential super case instance or null if no super case instance exists
     */
    //public function getSuperCaseInstanceId(): string;

    /**
     * The case instance id of a potential super case instance or null if no super case instance exists
     */
    //public function getCaseInstanceId(): string;

    /**
     * The id of the tenant this historic process instance belongs to. Can be <code>null</code>
     * if the historic process instance belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Return current state of HistoricProcessInstance, following values are recognized during process engine operations:
     *
     *  STATE_ACTIVE - running process instance
     *  STATE_SUSPENDED - suspended process instances
     *  STATE_COMPLETED - completed through normal end event
     *  STATE_EXTERNALLY_TERMINATED - terminated externally, for instance through REST API
     *  STATE_INTERNALLY_TERMINATED - terminated internally, for instance by terminating boundary event
     */
    public function getState(): string;
}
