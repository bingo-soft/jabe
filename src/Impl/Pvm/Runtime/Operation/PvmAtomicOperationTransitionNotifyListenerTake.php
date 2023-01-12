<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationTransitionNotifyListenerTake extends AbstractPvmAtomicOperationTransitionNotifyListenerTake
{

    public function isAsync(CoreExecution $execution): bool
    {
        return $execution->getActivity()->isAsyncAfter();
    }

    public function getCanonicalName(): ?string
    {
        return "transition-notify-listener-take";
    }

    public function isAsyncCapable(): bool
    {
        return true;
    }

    public function shouldHandleFailureAsBpmnError(): bool
    {
        return true;
    }
}
