<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Bpmn\Helper\EscalationHandler;
use Jabe\Impl\Bpmn\Parser\{
    Escalation,
    EscalationEventDefinition
};
use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ThrowEscalationEventActivityBehavior extends AbstractBpmnActivityBehavior
{
    protected $escalation;

    public function __construct(Escalation $escalation)
    {
        $this->escalation = $escalation;
    }

    public function execute(ActivityExecutionInterface $execution): void
    {
        $currentActivity = $execution->getActivity();

        $escalationEventDefinition = EscalationHandler::executeEscalation($execution, $this->escalation->getEscalationCode());

        if ($escalationEventDefinition === null || !$escalationEventDefinition->isCancelActivity()) {
            $this->leaveExecution($execution, $currentActivity, $escalationEventDefinition);
        }
    }

    protected function leaveExecution(ActivityExecutionInterface $execution, PvmActivityInterface $currentActivity, EscalationEventDefinition $escalationEventDefinition): void
    {
        // execution tree could have been expanded by triggering a non-interrupting event
        $replacingExecution = $execution->getReplacedBy();

        $leavingExecution = $replacingExecution ?? $execution;
        $this->leave($leavingExecution);
    }
}
