<?php

namespace Jabe\Engine\Impl\Bpmn\Delegate;

use Jabe\Engine\Impl\Delegate\DelegateInvocation;
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface
};

class ActivityBehaviorInvocation extends DelegateInvocation
{
    protected $behaviorInstance;

    protected $execution;

    public function __construct(ActivityBehaviorInterface $behaviorInstance, ActivityExecutionInterface $execution)
    {
        parent::__construct($execution, null);
        $this->behaviorInstance = $behaviorInstance;
        $this->execution = $execution;
    }

    protected function invoke(): void
    {
        $this->behaviorInstance->execute($execution);
    }
}
