<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class IntermediateCatchLinkEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    public function execute(ActivityExecutionInterface $execution): void
    {
        // a link event does not behave as a wait state
        $this->leave($execution);
    }
}
