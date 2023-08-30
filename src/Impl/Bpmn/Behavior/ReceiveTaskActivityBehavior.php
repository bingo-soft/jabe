<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ReceiveTaskActivityBehavior extends TaskActivityBehavior
{
    public function __construct()
    {
        parent::__construct();
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
      // Do nothing: waitstate behavior
    }

    public function signal(/*ActivityExecutionInterface*/$execution, ?string $signalName = null, $data = null, array $processVariables = []): void
    {
        $this->leave($execution);
    }
}
