<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Bpmn\Delegate\{
    ActivityBehaviorInvocation,
    ActivityBehaviorSignalInvocation
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface,
    SignallableActivityBehaviorInterface
};

class CustomActivityBehavior implements ActivityBehaviorInterface, SignallableActivityBehaviorInterface
{
    protected $delegateActivityBehavior;

    public function __construct(ActivityBehaviorInterface $activityBehavior)
    {
        $this->delegateActivityBehavior = $activityBehavior;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ActivityBehaviorInvocation($this->delegateActivityBehavior, $execution));
    }

    public function signal(ActivityExecutionInterface $execution, string $signalEvent, $signalData): void
    {
        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ActivityBehaviorSignalInvocation($this->delegateActivityBehavior, $execution, $signalEvent, $signalData));
    }

    public function getDelegateActivityBehavior(): ActivityBehaviorInterface
    {
        return $this->delegateActivityBehavior;
    }
}
