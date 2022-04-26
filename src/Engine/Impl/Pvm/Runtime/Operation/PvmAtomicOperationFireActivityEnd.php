<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationFireActivityEnd extends AbstractPvmEventAtomicOperation
{
    public function getCanonicalName(): string
    {
        return "fire-activity-end";
    }

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
        // nothing to do
    }

    protected function isSkipNotifyListeners(CoreExecution $execution): bool
    {
        return $execution->hasFailedOnEndListeners();
    }
}
