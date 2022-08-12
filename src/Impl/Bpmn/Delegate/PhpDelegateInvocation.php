<?php

namespace Jabe\Impl\Bpmn\Delegate;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};
use Jabe\Impl\Delegate\DelegateInvocation;

class PhpDelegateInvocation extends DelegateInvocation
{
    protected $delegateInstance;
    protected $execution;

    public function __construct(PhpDelegateInterface $delegateInstance, DelegateExecutionInterface $execution)
    {
        parent::__construct($execution, null);
        $this->delegateInstance = $delegateInstance;
        $this->execution = $execution;
    }

    protected function invoke(): void
    {
        $this->delegateInstance->execute($this->execution);
    }
}
