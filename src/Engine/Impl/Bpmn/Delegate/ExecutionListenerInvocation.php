<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Delegate;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use BpmPlatform\Engine\Impl\Delegate\DelegateInvocation;

class ExecutionListenerInvocation extends DelegateInvocation
{
    protected $executionListenerInstance;
    protected $execution;

    public function __construct(ExecutionListenerInterface $executionListenerInstance, DelegateExecutionInterface $execution)
    {
        parent::__construct($execution, null);
        $this->executionListenerInstance = $executionListenerInstance;
        $this->execution = $execution;
    }

    protected function invoke(): void
    {
        $this->executionListenerInstance->notify($execution);
    }
}
