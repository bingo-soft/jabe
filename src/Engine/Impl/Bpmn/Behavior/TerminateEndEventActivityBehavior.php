<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class TerminateEndEventActivityBehavior extends FlowNodeActivityBehavior
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        // we are the last execution inside this scope: calling end() ends this scope.
        $execution->end(true);
    }
}
