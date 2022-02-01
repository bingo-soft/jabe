<?php

namespace BpmPlatform\Engine\History;

interface HistoricIncidentInterface
{
    /**
     * Returns the unique identifier for this incident.
     */
    public function getId(): string;

    /**
     * Time when the incident happened.
     */
    public function getCreateTime(): string;

    /**
     * Time when the incident has been resolved or deleted.
     */
    public function getEndTime(): string;

    /**
     * Returns the type of this incident to identify the
     * kind of incident.
     *
     * <p>
     *
     * For example: <code>failedJobs</code> will be returned
     * in the case of an incident, which identify failed job
     * during the execution of a process instance.
     */
    public function getIncidentType(): string;

    /**
     * Returns the incident message.
     */
    public function getIncidentMessage(): string;

    /**
     * Returns the specific execution on which this
     * incident has happened.
     */
    public function getExecutionId(): string;

    /**
     * Returns the id of the activity of the process instance
     * on which this incident has happened.
     */
    public function getActivityId(): string;

    /**
     * Returns the specific root process instance id of the process instance
     * on which this incident has happened.
     */
    public function getRootProcessInstanceId(): string;

    /**
     * Returns the specific process instance on which this
     * incident has happened.
     */
    public function getProcessInstanceId(): string;

    /**
     * Returns the id of the process definition of this
     * process instance on which the incident has happened.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Returns the key of the process definition of this
     * process instance on which the incident has happened.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * Returns the id of the incident on which this incident
     * has been triggered.
     */
    public function getCauseIncidentId(): string;

    /**
     * Returns the id of the root incident on which
     * this transitive incident has been triggered.
     */
    public function getRootCauseIncidentId(): string;

    /**
     * Returns the payload of this incident.
     */
    public function getConfiguration(): string;

    /**
     * Returns the history payload of this incident.
     */
    public function getHistoryConfiguration(): string;

    /**
     * Returns <code>true</code>, iff the corresponding incident
     * has not been deleted or resolved.
     */
    public function isOpen(): bool;

    /**
     * Returns <code>true</code>, iff the corresponding incident
     * has been <strong>deleted</strong>.
     */
    public function isDeleted(): bool;

    /**
     * Returns <code>true</code>, iff the corresponding incident
    * has been <strong>resolved</strong>.
    */
    public function isResolved(): bool;

    /**
     * Returns the id of the tenant this incident belongs to. Can be <code>null</code>
     * if the incident belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * Returns the id of the job definition the incident belongs to. Can be <code>null</code>
     * if the incident belongs to no job definition.
     */
    public function getJobDefinitionId(): string;

    /** The time the historic incident will be removed. */
    public function getRemovalTime(): string;

    /**
     * Returns the id of the activity on which the last exception occurred.
     */
    public function getFailedActivityId(): ?string;

    /**
     * Returns the annotation of this incident
     */
    public function getAnnotation(): string;
}
