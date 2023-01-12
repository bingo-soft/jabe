<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Pvm\Runtime\{
    ScopeInstantiationContext,
    PvmExecutionImpl
};
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationTransitionNotifyListenerStart extends PvmAtomicOperationActivityInstanceStart
{
    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getActivity();
    }

    protected function getEventName(): ?string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        parent::eventNotificationsCompleted($execution);

        $transition = $execution->getTransition();
        $destination = null;
        if ($transition === null) { // this is null after async cont. -> transition is not stored in execution
            $destination = $execution->getActivity();
        } else {
            $destination = $transition->getDestination();
        }
        $execution->setTransition(null);
        $execution->setActivity($destination);

        if ($execution->isProcessInstanceStarting()) {
            // only call this method if we are currently in the starting phase;
            // if not, this may make an unnecessary request to fetch the process
            // instance from the database
            $execution->setProcessInstanceStarting(false);
        }

        $execution->dispatchDelayedEventsAndPerformOperation(self::activityExecute());
    }

    public function getCanonicalName(): ?string
    {
        return "transition-notifiy-listener-start";
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
