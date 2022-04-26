<?php

namespace Jabe\Engine\Delegate;

interface BaseDelegateExecutionInterface extends VariableScopeInterface
{
    /** Unique id of this path of execution that can be used as a handle to provide external signals back into the engine after wait states. */
    public function getId(): string;

    /** The {@link ExecutionListener#EVENTNAME_START event name} in case this execution is passed in for an {@link ExecutionListener}  */
    public function getEventName(): string;

    /** The business key for the root execution (e.g. process instance).
     */
    public function getBusinessKey(): ?string;
}
