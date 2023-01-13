<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmTransitionInterface
};
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ParallelGatewayActivityBehavior extends GatewayActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    public function __construct()
    {
        parent::__construct();
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        // Join
        $activity = $execution->getActivity();
        $outgoingTransitions = $execution->getActivity()->getOutgoingTransitions();

        $execution->inactivate();
        $this->lockConcurrentRoot($execution);

        $joinedExecutions = $execution->findInactiveConcurrentExecutions($activity);
        $nbrOfExecutionsToJoin = count($execution->getActivity()->getIncomingTransitions());
        $nbrOfExecutionsJoined = count($joinedExecutions);

        if ($nbrOfExecutionsJoined == $nbrOfExecutionsToJoin) {
            // Fork
            //LOG.activityActivation(activity.getId(), nbrOfExecutionsJoined, nbrOfExecutionsToJoin);
            $execution->leaveActivityViaTransitions($outgoingTransitions, $joinedExecutions);
        } else {
            //LOG.noActivityActivation(activity.getId(), nbrOfExecutionsJoined, nbrOfExecutionsToJoin);
        }
    }
}
