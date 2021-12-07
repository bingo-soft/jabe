<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class IntermediateCatchLinkEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        // a link event does not behave as a wait state
        $this->leave($execution);
    }
}
