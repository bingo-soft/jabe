<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

abstract class PvmAtomicOperationCreateConcurrentExecution implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    public function execute(PvmExecutionImpl $execution): void
    {
        // Invariant: execution is the Scope Execution for the activity's flow scope.
        $activityToStart = $execution->getNextActivity();
        $execution->setNextActivity(null);
        $propagatingExecution = $execution->createConcurrentExecution();
        // set next activity on propagating execution
        $propagatingExecution->setActivity($activityToStart);
        $this->concurrentExecutionCreated($propagatingExecution);
    }

    abstract protected function concurrentExecutionCreated(PvmExecutionImpl $propagatingExecution): void;

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return false;
    }
}
