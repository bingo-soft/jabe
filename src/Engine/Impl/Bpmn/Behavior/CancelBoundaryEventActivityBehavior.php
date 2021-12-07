<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Runtime\LegacyBehavior;

class CancelBoundaryEventActivityBehavior extends BoundaryEventActivityBehavior
{
    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        if (LegacyBehavior::signalCancelBoundaryEvent($signalName)) {
            // join compensating executions
            if (!$execution->hasChildren()) {
                $this->leave($execution);
            } else {
                $execution->forceUpdate();
            }
        } else {
            parent::signal($execution, $signalName, $signalData);
        }
    }
}
