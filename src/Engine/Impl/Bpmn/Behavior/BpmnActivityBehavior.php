<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\{
    ConditionInterface,
    ProcessEngineLogger
};
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Pvm\PvmTransitionInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
    CompensationBehavior,
    PvmExecutionImpl
};

class BpmnActivityBehavior
{
    //protected static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    /**
     * Performs the default outgoing BPMN 2.0 behavior, which is having parallel
     * paths of executions for the outgoing sequence flow.
     *
     * More precisely: every sequence flow that has a condition which evaluates to
     * true (or which doesn't have a condition), is selected for continuation of
     * the process instance. If multiple sequencer flow are selected, multiple,
     * parallel paths of executions are created.
     */
    public function performDefaultOutgoingBehavior(ActivityExecutionInterface $activityExecution): void
    {
        $this->performOutgoingBehavior($activityExecution, true, null);
    }

    /**
     * Performs the default outgoing BPMN 2.0 behavior (@see
     * {@link #performDefaultOutgoingBehavior(ActivityExecution)}), but without
     * checking the conditions on the outgoing sequence flow.
     *
     * This means that every outgoing sequence flow is selected for continuing the
     * process instance, regardless of having a condition or not. In case of
     * multiple outgoing sequence flow, multiple parallel paths of executions will
     * be created.
     */
    public function performIgnoreConditionsOutgoingBehavior(ActivityExecutionInterface $activityExecution): void
    {
        $this->performOutgoingBehavior($activityExecution, false, null);
    }

    /**
     * Actual implementation of leaving an activity.
     *
     * @param execution
     *          The current execution context
     * @param checkConditions
     *          Whether or not to check conditions before determining whether or
     *          not to take a transition.
     */
    protected function performOutgoingBehavior(
        ActivityExecutionInterface $execution,
        bool $checkConditions,
        array $reusableExecutions
    ): void {

        //LOG.leavingActivity(execution.getActivity().getId());

        $defaultSequenceFlow = $execution->getActivity()->getProperty("default");
        $transitionsToTake = [];

        $outgoingTransitions = $execution->getActivity()->getOutgoingTransitions();
        foreach ($outgoingTransitions as $outgoingTransition) {
            if ($defaultSequenceFlow == null || $outgoingTransition->getId() != $defaultSequenceFlow) {
                $condition = $outgoingTransition->getProperty(BpmnParse::PROPERTYNAME_CONDITION);
                if ($condition == null || !$checkConditions || $condition->evaluate($execution)) {
                    $transitionsToTake[] = $outgoingTransition;
                }
            }
        }
        if (count($transitionsToTake) == 1) {
            $execution->leaveActivityViaTransition($transitionsToTake[0]);
        } elseif (count($transitionsToTake) > 1) {
            if ($reusableExecutions == null || empty($reusableExecutions)) {
                $execution->leaveActivityViaTransitions($transitionsToTake, [$execution]);
            } else {
                $execution->leaveActivityViaTransitions($transitionsToTake, $reusableExecutions);
            }
        } else {
            if ($defaultSequenceFlow != null) {
                $defaultTransition = $execution->getActivity()->findOutgoingTransition($defaultSequenceFlow);
                if ($defaultTransition != null) {
                    $execution->leaveActivityViaTransition($defaultTransition);
                } else {
                    //throw LOG.missingDefaultFlowException(execution.getActivity().getId(), defaultSequenceFlow);
                }
            } elseif (!empty($outgoingTransitions)) {
                //throw LOG.missingConditionalFlowException(execution.getActivity().getId());
            } else {
                if ($execution->getActivity()->isCompensationHandler() && $this->isAncestorCompensationThrowing($execution)) {
                    $execution->endCompensation();
                } else {
                    //LOG.missingOutgoingSequenceFlow(execution.getActivity().getId());
                    $execution->end(true);
                }
            }
        }
    }

    protected function isAncestorCompensationThrowing(ActivityExecutionInterface $execution): bool
    {
        $parent = $execution->getParent();
        while ($parent != null) {
            if (CompensationBehavior::isCompensationThrowing($parent)) {
                return true;
            }
            $parent = $parent->getParent();
        }
        return false;
    }
}
