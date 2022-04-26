<?php

namespace Jabe\Engine\Runtime;

interface ProcessInstanceInterface extends ExecutionInterface
{
    /**
     * The id of the process definition of the process instance.
     */
    public function getProcessDefinitionId(): string;

    /**
     * The business key of this process instance.
     */
    public function getBusinessKey(): ?string;

    /**
     * The id of the root process instance associated with this process instance.
     */
    public function getRootProcessInstanceId(): string;

    /**
     * The id of the case instance associated with this process instance.
     */
    public function getCaseInstanceId(): string;

    /**
     * returns true if the process instance is suspended
     */
    public function isSuspended(): bool;
}
