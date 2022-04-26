<?php

namespace Jabe\Engine\Impl\Bpmn\Delegate;

use Jabe\Engine\Impl\Delegate\DelegateInvocation;
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    SignallableActivityBehaviorInterface
};

class ActivityBehaviorSignalInvocation extends DelegateInvocation
{
    protected $behaviorInstance;
    protected $execution;
    protected $signalName;
    protected $signalData;

    public function __construct(SignallableActivityBehaviorInterface $behaviorInstance, ActivityExecutionInterface $execution, string $signalName, $signalData)
    {
        parent::__construct($execution, null);
        $this->behaviorInstance = $behaviorInstance;
        $this->execution = $execution;
        $this->signalName = $signalName;
        $this->signalData = $signalData;
    }

    protected function invoke(): void
    {
        $this->behaviorInstance->signal($this->execution, $this->signalName, $this->signalData);
    }
}
