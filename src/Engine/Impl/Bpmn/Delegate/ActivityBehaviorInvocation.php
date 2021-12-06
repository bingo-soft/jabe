<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Delegate;

use BpmPlatform\Engine\Impl\Delegate\DelegateInvocation;
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
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
