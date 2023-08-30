<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class TerminateEndEventActivityBehavior extends FlowNodeActivityBehavior
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        // we are the last execution inside this scope: calling end() ends this scope.
        $execution->end(true);
    }
}
