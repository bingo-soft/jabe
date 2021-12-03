<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationTransitionNotifyListenerTake extends AbstractPvmAtomicOperationTransitionNotifyListenerTake
{

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return $execution->getActivity()->isAsyncAfter();
    }

    public function getCanonicalName(): string
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
