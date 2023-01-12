<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class NoneEndEventActivityBehavior extends FlowNodeActivityBehavior
{
    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        $execution->end(true);
    }
}
