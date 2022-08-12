<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmLogger
};
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

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
            $propagatingExecution->setActivity($activity);
            $propagatingExecution->setTransition($execution->getTransition());
            $execution->setTransition(null);
            $execution->setActive(false);
            $execution->setActivity(null);
            //LOG.createScope(execution, propagatingExecution);
            $propagatingExecution->initialize();
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
