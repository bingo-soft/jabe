<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    PhpDelegateInterface
};
use BpmPlatform\Engine\Impl\Bpmn\Delegate\PhpDelegateInvocation;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface
};

class ServiceTaskPhpDelegateActivityBehavior extends TaskActivityBehavior implements ActivityBehaviorInterface, ExecutionListenerInterface
{
    protected $phpDelegate;

    public function __construct(?PhpDelegateInterface $phpDelegate)
    {
        $this->phpDelegate = $phpDelegate;
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $this->execute($execution);
        $this->leave($execution);
    }

    public function notify(DelegateExecutionInterface $execution): void
    {
        $this->execute($execution);
    }

    public function execute(DelegateExecutionInterface $execution): void
    {
        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new PhpDelegateInvocation($this->phpDelegate, $execution));
    }
}
