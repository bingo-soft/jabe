<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Pvm\Runtime\{
    CallbackInterface,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationActivityNotifyListenerEnd extends PvmAtomicOperationActivityInstanceEnd
{
    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getActivity();
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_END;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        // perform activity end behavior
        $execution->dispatchDelayedEventsAndPerformOperation(new class () implements CallbackInterface {
            public function callback($execution)
            {
                $execution->leaveActivityInstance();
                $execution->setActivityInstanceId(null);
                $execution->performOperation(PvmAtomicOperationActivityNotifyListenerEnd::activityEnd());
                return null;
            }
        });
    }

    public function getCanonicalName(): string
    {
        return "activity-notify-listener-end";
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
