<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;
use Jabe\Impl\Pvm\Runtime\{
    CallbackInterface,
    ScopeInstantiationContext,
    PvmExecutionImpl
};

class PvmAtomicOperationTransitionNotifyListenerEnd extends PvmAtomicOperationActivityInstanceEnd
{
    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getActivity();
    }

    protected function getEventName(): ?string
    {
        return ExecutionListenerInterface::EVENTNAME_END;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        if ($execution->isProcessInstanceStarting()) {
            // only call this method if we are currently in the starting phase;
            // if not, this may make an unnecessary request to fetch the process
            // instance from the database
            $execution->setProcessInstanceStarting(false);
        }

        $execution->dispatchDelayedEventsAndPerformOperation(new class () implements CallbackInterface {
            public function callback($execution)
            {
                $execution->leaveActivityInstance();
                $execution->performOperation(PvmAtomicOperationTransitionNotifyListenerEnd::transitionDestroyScope());
                return null;
            }
        });
    }

    public function getCanonicalName(): ?string
    {
        return "transition-notify-listener-end";
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
