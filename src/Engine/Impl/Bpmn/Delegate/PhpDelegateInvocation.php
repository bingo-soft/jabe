<?php

namespace Jabe\Engine\Impl\Bpmn\Delegate;

use Jabe\Engine\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};
use Jabe\Engine\Impl\Delegate\DelegateInvocation;

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
