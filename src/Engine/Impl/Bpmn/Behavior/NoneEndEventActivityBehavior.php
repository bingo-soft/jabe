<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class NoneEndEventActivityBehavior extends FlowNodeActivityBehavior
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        $execution->end(true);
    }
}
