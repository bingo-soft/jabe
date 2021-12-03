<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\PvmActivityInterface;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationsTransitionInterruptFlowScope extends PvmAtomicOperationInterruptScope
{
    public function getCanonicalName(): string
    {
        return "transition-interrupt-scope";
    }

    protected function scopeInterrupted(PvmExecutionImpl $execution): void
    {
        $execution->dispatchDelayedEventsAndPerformOperation(self::transitionCreateScope());
    }

    protected function getInterruptingActivity(PvmExecutionImpl $execution): PvmActivityInterface
    {
        return $execution->getTransition()->getDestination();
    }
}
