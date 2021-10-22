<?php

namespace BpmPlatform\Engine\History;

interface HistoricDetailInterface
{
    /** The unique DB id for this historic detail */
    public function getId(): string;

    /** The process definition key reference. */
    public function getProcessDefinitionKey(): string;

    /** The process definition reference. */
    public function getProcessDefinitionId(): string;

    /** The root process instance reference */
    public function getRootProcessInstanceId(): string;

    /** The process instance reference. */
    public function getProcessInstanceId(): string;

    /** The activity reference in case this detail is related to an activity instance. */
    public function getActivityInstanceId(): string;

    /** The identifier for the path of execution. */
    public function getExecutionId(): string;

    /** The identifier for the task. */
    public function getTaskId();

    /** The time when this detail occurred */
    public function getTime(): string;

    /**
     * The id of the tenant this historic detail belongs to. Can be <code>null</code>
     * if the historic detail belongs to no single tenant.
     */
    public function getTenantId(): ?string;

    /**
     * The id of operation. Helps to link records in different historic tables.
     * References operationId of user operation log entry.
     */
    public function getUserOperationId(): string;

    /** The time the historic detail will be removed. */
    public function getRemovalTime(): string;
}
