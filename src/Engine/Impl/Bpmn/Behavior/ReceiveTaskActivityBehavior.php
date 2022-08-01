<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ReceiveTaskActivityBehavior extends TaskActivityBehavior
{
    public function performExecution(ActivityExecutionInterface $execution): void
    {
      // Do nothing: waitstate behavior
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $data): void
    {
        $this->leave($execution);
    }
}
