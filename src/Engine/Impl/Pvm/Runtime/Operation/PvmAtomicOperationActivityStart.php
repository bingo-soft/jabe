<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationActivityStart extends PvmAtomicOperationActivityInstanceStart
{
    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        parent::eventNotificationsCompleted($execution);

        $execution->dispatchDelayedEventsAndPerformOperation(self::activityExecute());
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getActivity();
    }

    public function getCanonicalName(): string
    {
        return "activity-start";
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
