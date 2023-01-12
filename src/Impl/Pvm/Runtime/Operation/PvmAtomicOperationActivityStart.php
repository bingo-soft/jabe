<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationActivityStart extends PvmAtomicOperationActivityInstanceStart
{
    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        parent::eventNotificationsCompleted($execution);

        $execution->dispatchDelayedEventsAndPerformOperation(self::activityExecute());
    }

    protected function getEventName(): ?string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getActivity();
    }

    public function getCanonicalName(): ?string
    {
        return "activity-start";
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
