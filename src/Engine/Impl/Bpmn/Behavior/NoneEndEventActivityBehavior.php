<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class NoneEndEventActivityBehavior extends FlowNodeActivityBehavior
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        $execution->end(true);
    }
}
