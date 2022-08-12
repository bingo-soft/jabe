<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

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
