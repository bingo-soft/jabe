<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

abstract class PvmAtomicOperationInterruptScope implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    public function execute(PvmExecutionImpl $execution): void
    {
        $interruptingActivity = $this->getInterruptingActivity($execution);

        $scopeExecution = !$execution->isScope() ? $execution->getParent() : $execution;

        if ($scopeExecution != $execution) {
            // remove the current execution before interrupting and continuing executing the interrupted activity
            // reason:
            //   * interrupting should not attempt to fire end events for this execution
            //   * the interruptingActivity is executed with the scope execution
            $execution->remove();
        }

        $scopeExecution->interrupt("Interrupting activity " . $interruptingActivity . " executed.");

        $scopeExecution->setActivity($interruptingActivity);
        $scopeExecution->setActive(true);
        $scopeExecution->setTransition($execution->getTransition());
        $this->scopeInterrupted($scopeExecution);
    }

    abstract protected function scopeInterrupted(PvmExecutionImpl $execution): void;

    abstract protected function getInterruptingActivity(PvmExecutionImpl $execution): PvmActivityInterface;

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return false;
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }
}
