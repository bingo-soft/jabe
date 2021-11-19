<?php

namespace BpmPlatform\Engine\Runtime;

interface ExecutionInterface
{
    /**
     * The unique identifier of the execution.
     */
    public function getId(): string;

    /**
     * Indicates if the execution is suspended.
     */
    public function isSuspended(): bool;

    /**
     * Indicates if the execution is ended.
     */
    public function isEnded(): bool;

    /** Id of the root of the execution tree representing the process instance.
     * It is the same as #getId() if this execution is the process instance. */
    public function getProcessInstanceId(): string;

    /**
     * The id of the tenant this execution belongs to. Can be <code>null</code>
     * if the execution belongs to no single tenant.
     */
    public function getTenantId(): ?string;
}
