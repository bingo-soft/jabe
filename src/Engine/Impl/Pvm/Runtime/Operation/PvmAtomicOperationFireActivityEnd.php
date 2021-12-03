<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Delegate\ExecutionListenerInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ScopeImpl;
use BpmPlatform\Engine\Impl\Core\Model\CoreModelElement;
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;

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
