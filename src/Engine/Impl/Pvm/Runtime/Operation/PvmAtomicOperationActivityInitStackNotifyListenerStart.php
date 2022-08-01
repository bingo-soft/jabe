<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    ModificationObserverBehaviorInterface
};
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Engine\Impl\Pvm\Runtime\{
    ScopeInstantiationContext,
    InstantiationStack,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationActivityInitStackNotifyListenerStart extends PvmAtomicOperationActivityInstanceStart
{
    public function getCanonicalName(): string
    {
        return "activity-init-stack-notify-listener-start";
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        $activity = $execution->getActivity();
        if ($activity !== null) {
            return $activity;
        } else {
            $parent = $execution->getParent();
            if ($parent !== null) {
                return $this->getScope($execution->getParent());
            }
            return $execution->getProcessDefinition();
        }
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        parent::eventNotificationsCompleted($execution);

        $execution->activityInstanceStarted();

        $startContext = $execution->getScopeInstantiationContext();
        $instantiationStack = $startContext->getInstantiationStack();

        $propagatingExecution = $execution;
        $activity = $execution->getActivity();
        if ($activity->getActivityBehavior() instanceof ModificationObserverBehaviorInterface) {
            $behavior = $activity->getActivityBehavior();
            $concurrentExecutions = $behavior->initializeScope($propagatingExecution, 1);
            $propagatingExecution = $concurrentExecutions[0];
        }

        // if the stack has been instantiated
        if (empty($instantiationStack->getActivities()) && $instantiationStack->getTargetActivity() !== null) {
            // as if we are entering the target activity instance id via a transition
            $propagatingExecution->setActivityInstanceId(null);

            // execute the target activity with this execution
            $startContext->applyVariables($propagatingExecution);
            $propagatingExecution->setActivity($instantiationStack->getTargetActivity());
            $propagatingExecution->disposeScopeInstantiationContext();
            $propagatingExecution->performOperation(self::activityStartCreateScope());
        } elseif (empty($instantiationStack->getActivities()) && $instantiationStack->getTargetTransition() !== null) {
            // as if we are entering the target activity instance id via a transition
            $propagatingExecution->setActivityInstanceId(null);

            // execute the target transition with this execution
            $transition = $instantiationStack->getTargetTransition();
            $startContext->applyVariables($propagatingExecution);
            $propagatingExecution->setActivity($transition->getSource());
            $propagatingExecution->setTransition($transition);
            $propagatingExecution->disposeScopeInstantiationContext();
            $propagatingExecution->performOperation(self::transitionStartNotifyListenerTake());
        } else {
            // else instantiate the activity stack further
            $propagatingExecution->setActivity(null);
            $propagatingExecution->performOperation(self::activityInitStack());
        }
    }
}
