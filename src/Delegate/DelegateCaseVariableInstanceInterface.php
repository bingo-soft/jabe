<?php

namespace Jabe\Delegate;

interface DelegateCaseVariableInstanceInterface extends DelegateVariableInstanceInterface
{
    public function getEventName(): ?string;

    /** The case execution in which context the variable was created/updated/deleted. */
    public function getSourceExecution(): DelegateCaseExecutionInterface;
}
