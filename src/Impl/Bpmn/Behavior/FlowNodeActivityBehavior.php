<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    SignallableActivityBehaviorInterface
};
use Jabe\Impl\Pvm\Runtime\AtomicOperation;

abstract class FlowNodeActivityBehavior implements SignallableActivityBehaviorInterface
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $bpmnActivityBehavior;

    public function __construct()
    {
        $this->bpmnActivityBehavior = new BpmnActivityBehavior();
    }

    /**
     * Default behaviour: just leave the activity with no extra functionality.
     */
    public function execute(ActivityExecutionInterface $execution): void
    {
        $this->leave($execution);
    }

    /**
     * Default way of leaving a BPMN 2.0 activity: evaluate the conditions on the
     * outgoing sequence flow and take those that evaluate to true.
     */
    public function leave(ActivityExecutionInterface $execution): void
    {
        $execution->dispatchDelayedEventsAndPerformOperation(AtomicOperation::activityLeave());
    }

    public function doLeave(ActivityExecutionInterface $execution): void
    {
        $this->bpmnActivityBehavior->performDefaultOutgoingBehavior($execution);
    }

    protected function leaveIgnoreConditions(ActivityExecutionInterface $activityContext): void
    {
        $this->bpmnActivityBehavior->performIgnoreConditionsOutgoingBehavior($activityContext);
    }

    public function signal(ActivityExecutionInterface $execution, string $signalName, $signalData): void
    {
        // concrete activity behaviors that do accept signals should override this method;
        //throw LOG.unsupportedSignalException(execution.getActivity().getId());
    }
}
