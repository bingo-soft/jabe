<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\RuntimeServiceInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

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
