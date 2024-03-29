<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Delegate\{
    ActivityBehaviorInvocation,
    ActivityBehaviorSignalInvocation
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Pvm\Delegate\{
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

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation(new ActivityBehaviorInvocation($this->delegateActivityBehavior, $execution));
    }

    public function signal(/*ActivityExecutionInterface*/$execution, ?string $signalEvent= null, $signalData = null, array $processVariables = []): void
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
