<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Runtime\LegacyBehavior;

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
