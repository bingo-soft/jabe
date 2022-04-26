<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

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
