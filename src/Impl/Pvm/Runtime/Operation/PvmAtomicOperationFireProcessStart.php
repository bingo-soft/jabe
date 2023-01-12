<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Delegate\ExecutionListenerInterface;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationFireProcessStart extends AbstractPvmEventAtomicOperation
{
    public function getCanonicalName(): ?string
    {
        return "fire-process-start";
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        return $execution->getProcessDefinition();
    }

    protected function getEventName(): ?string
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
