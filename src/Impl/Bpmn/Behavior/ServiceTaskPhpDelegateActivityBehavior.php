<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface,
    PhpDelegateInterface
};
use Jabe\Impl\Bpmn\Delegate\PhpDelegateInvocation;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface
};

class ServiceTaskPhpDelegateActivityBehavior extends TaskActivityBehavior implements ActivityBehaviorInterface, ExecutionListenerInterface
{
    protected $phpDelegate;

    public function __construct(?PhpDelegateInterface $phpDelegate)
    {
        parent::__construct();
        $this->phpDelegate = $phpDelegate;
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $this->executeDelegate($execution);
        $this->leave($execution);
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        $this->executeDelegate($execution);
    }

    public function execute(/*DelegateExecutionInterface*/$execution): void
    {
        if ($execution instanceof ActivityExecutionInterface) {
            parent::execute($execution);
        } else {
            $this->executeDelegate($execution);
        }
    }

    private function executeDelegate(/*DelegateExecutionInterface*/$execution): void
    {
        Context::getProcessEngineConfiguration()
        ->getDelegateInterceptor()
        ->handleInvocation(new PhpDelegateInvocation($this->phpDelegate, $execution));
    }
}
