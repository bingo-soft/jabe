<?php

namespace BpmPlatform\Engine\Delegate;

interface DelegateVariableInstanceInterface
{
    public function getEventName(): string;

    public function getSourceExecution(): BaseDelegateExecutionInterface;
}
