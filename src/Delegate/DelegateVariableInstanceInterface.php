<?php

namespace Jabe\Delegate;

interface DelegateVariableInstanceInterface
{
    public function getEventName(): ?string;

    public function getSourceExecution(): BaseDelegateExecutionInterface;
}
