<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmLogger
};
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

abstract class PvmAtomicOperationCreateScope implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    //private final static PvmLogger LOG = PvmLogger.PVM_LOGGER;

    public function execute(PvmExecutionImpl $execution): void
    {
        // reset activity instance id before creating the scope
        $execution->setActivityInstanceId($execution->getParentActivityInstanceId());

        $propagatingExecution = null;
        $activity = $execution->getActivity();
        if ($activity->isScope()) {
            $propagatingExecution = $execution->createExecution();
            $propagatingEexecution->setActivity($activity);
            $propagatingEexecution->setTransition($execution->getTransition());
            $execution->setTransition(null);
            $execution->setActive(false);
            $execution->setActivity(null);
            //LOG.createScope(execution, propagatingExecution);
            $propagatingEexecution->initialize();
        } else {
            $propagatingExecution = $execution;
        }


        $this->scopeCreated($propagatingExecution);
    }

    /**
     * Called with the propagating execution
     */
    abstract protected function scopeCreated(PvmExecutionImpl $execution): void;
}
