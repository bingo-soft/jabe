<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\{
    ConditionInterface,
    ProcessEngineLogger
};
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmTransitionInterface
};
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};

class InclusiveGatewayActivityBehavior extends GatewayActivityBehavior
{
    //protected static BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    public function execute(ActivityExecutionInterface $execution): void
    {
        $execution->inactivate();
        $this->lockConcurrentRoot($execution);

        $activity = $execution->getActivity();
        if ($this->activatesGateway($execution, $activity)) {
            //LOG.activityActivation(activity.getId());

            $joinedExecutions = $execution->findInactiveConcurrentExecutions($activity);
            $defaultSequenceFlow = $execution->getActivity()->getProperty("default");
            $transitionsToTake = [];

            // find matching non-default sequence flows
            foreach ($execution->getActivity()->getOutgoingTransitions() as $outgoingTransition) {
                if ($defaultSequenceFlow == null || $outgoingTransition->getId() != $defaultSequenceFlow) {
                    $condition = $outgoingTransition->getProperty(BpmnParse::PROPERTYNAME_CONDITION);
                    if ($condition == null || $condition->evaluate($execution)) {
                        $transitionsToTake[] = $outgoingTransition;
                    }
                }
            }

            // if none found, add default flow
            if (empty($transitionsToTake)) {
                if ($defaultSequenceFlow != null) {
                    $defaultTransition = $execution->getActivity()->findOutgoingTransition($defaultSequenceFlow);
                    if ($defaultTransition == null) {
                        //throw LOG.missingDefaultFlowException(execution.getActivity().getId(), defaultSequenceFlow);
                    }

                    $transitionsToTake[] = $defaultTransition;
                } else {
                    // No sequence flow could be found, not even a default one
                    //throw LOG.stuckExecutionException(execution.getActivity().getId());
                }
            }

            // take the flows found
            $execution->leaveActivityViaTransitions($transitionsToTake, $joinedExecutions);
        } else {
            //LOG.noActivityActivation(activity.getId());
        }
    }

    protected function getLeafExecutions(ActivityExecutionInterface $parent): array
    {
        $executionlist = [];
        $subExecutions = $parent->getNonEventScopeExecutions();
        if (count($subExecutions) == 0) {
            $executionlist[] = $parent;
        } else {
            foreach ($subExecutions as $concurrentExecution) {
                $executionlist = array_merge($executionlist, $this->getLeafExecutions($concurrentExecution));
            }
        }
        return $executionlist;
    }

    protected function activatesGateway(ActivityExecutionInterface $execution, PvmActivityInterface $gatewayActivity): bool
    {
        $numExecutionsGuaranteedToActivate = count($gatewayActivity->getIncomingTransitions());
        $scopeExecution = $execution->isScope() ? $execution : $execution->getParent();

        $executionsAtGateway = $execution->findInactiveConcurrentExecutions($gatewayActivity);

        if (count($executionsAtGateway) >= $numExecutionsGuaranteedToActivate) {
            return true;
        } else {
            $executionsNotAtGateway = $this->getLeafExecutions($scopeExecution);
            foreach ($executionsAtGateway as $executionToDel) {
                foreach ($executionsNotAtGateway as $key => $curExecution) {
                    if ($curExecution == $executionToDel) {
                        unset($executionsNotAtGateway[$key]);
                        break;
                    }
                }
            }

            foreach ($executionsNotAtGateway as $executionNotAtGateway) {
                if ($this->canReachActivity($executionNotAtGateway, $gatewayActivity)) {
                    return false;
                }
            }

            // if no more token may arrive, then activate
            return true;
        }
    }

    protected function canReachActivity(ActivityExecutionInterface $execution, PvmActivityInterface $activity): bool
    {
        $pvmTransition = $execution->getTransition();
        if ($pvmTransition != null) {
            return $this->isReachable($pvmTransition->getDestination(), $activity, []);
        } else {
            return $this->isReachable($execution->getActivity(), $activity, []);
        }
    }

    protected function isReachable(PvmActivityInterface $srcActivity, PvmActivityInterface $targetActivity, array $visitedActivities): bool
    {
        if ($srcActivity == $targetActivity) {
            return true;
        }

        foreach ($visitedActivities as $curActivity) {
            if ($curActivity == $srcActivity) {
                return false;
            }
        }

        // To avoid infinite looping, we must capture every node we visit and
        // check before going further in the graph if we have already visited the node.
        $visitedActivities[] = $srcActivity;

        $outgoingTransitions = $srcActivity->getOutgoingTransitions();

        if (empty($outgoingTransitions)) {
            if ($srcActivity->getActivityBehavior() instanceof EventBasedGatewayActivityBehavior) {
                $eventBasedGateway = $srcActivity;
                $eventActivities = $eventBasedGateway->getEventActivities();

                foreach ($eventActivities as $eventActivity) {
                    $isReachable = $this->isReachable($eventActivity, $targetActivity, $visitedActivities);

                    if ($isReachable) {
                        return true;
                    }
                }
            } else {
                $flowScope = $srcActivity->getFlowScope();
                if ($flowScope != null && $flowScope instanceof PvmActivityInterface) {
                    return $this->isReachable($flowScope, $targetActivity, $visitedActivities);
                }
            }
            return false;
        } else {
            foreach ($outgoingTransitions as $pvmTransition) {
                $destinationActivity = $pvmTransition->getDestination();
                $contains = false;
                foreach ($visitedActivities as $curActivity) {
                    if ($curActivity == $destinationActivity) {
                        $contains = true;
                        break;
                    }
                }
                if ($destinationActivity != null && !$contains) {
                    $reachable = $this->isReachable($destinationActivity, $targetActivity, $visitedActivities);
                    // If false, we should investigate other paths, and not yet return the result
                    if ($reachable) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
