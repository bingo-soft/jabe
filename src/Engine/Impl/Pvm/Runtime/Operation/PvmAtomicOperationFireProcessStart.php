<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Delegate\ExecutionListenerInterface;
use BpmPlatform\Engine\Impl\Core\Model\CoreModelElement;
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationFireProcessStart extends AbstractPvmEventAtomicOperation
{
    public function getCanonicalName(): string
    {
        return "fire-process-start";
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getProcessDefinition();
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_START;
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
