<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\{
    ConditionInterface,
    ProcessEngineLogger
};
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Pvm\PvmTransitionInterface;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ExclusiveGatewayActivityBehavior extends GatewayActivityBehavior
{
    //protected static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    /**
     * The default behaviour of BPMN, taking every outgoing sequence flow
     * (where the condition evaluates to true), is not valid for an exclusive
     * gateway.
     *
     * Hence, this behaviour is overriden and replaced by the correct behavior:
     * selecting the first sequence flow which condition evaluates to true
     * (or which hasn't got a condition) and leaving the activity through that
     * sequence flow.
     *
     * If no sequence flow is selected (ie all conditions evaluate to false),
     * then the default sequence flow is taken (if defined).
     */
    public function doLeave(ActivityExecutionInterface $execution): void
    {
        //LOG.leavingActivity(execution.getActivity().getId());

        $outgoingSeqFlow = null;
        $defaultSequenceFlow = $execution->getActivity()->getProperty("default");
        $transitionIterator = $execution->getActivity()->getOutgoingTransitions();
        foreach ($transitionIterator as $seqFlow) {
            $condition = $seqFlow->getProperty(BpmnParse::PROPERTYNAME_CONDITION);
            if (
                ($condition == null && ($defaultSequenceFlow == null || !$defaultSequenceFlow == $seqFlow->getId())) ||
                ($condition != null && $condition->evaluate($execution))
            ) {
                //LOG.outgoingSequenceFlowSelected(seqFlow.getId());
                $outgoingSeqFlow = $seqFlow;
            }
        }

        if ($outgoingSeqFlow != null) {
            $execution->leaveActivityViaTransition($outgoingSeqFlow);
        } else {
            if ($defaultSequenceFlow != null) {
                $defaultTransition = $execution->getActivity()->findOutgoingTransition($defaultSequenceFlow);
                if ($defaultTransition != null) {
                    $execution->leaveActivityViaTransition($defaultTransition);
                } else {
                    //throw LOG.missingDefaultFlowException(execution.getActivity().getId(), defaultSequenceFlow);
                }
            } else {
                //No sequence flow could be found, not even a default one
                //throw LOG.stuckExecutionException(execution.getActivity().getId());
            }
        }
    }
}
