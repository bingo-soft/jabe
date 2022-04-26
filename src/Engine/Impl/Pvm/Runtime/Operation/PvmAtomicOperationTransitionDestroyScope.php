<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Pvm\{
    PvmActivityInterface,
    PvmLogger,
    PvmTransitionInterface
};
use Jabe\Engine\Impl\Pvm\Runtime\{
    LegacyBehavior,
    OutgoingExecution,
    PvmExecutionImpl
};

class PvmAtomicOperationTransitionDestroyScope implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    //private final static PvmLogger LOG = ProcessEngineLogger.PVM_LOGGER;

    public function isAsync(PvmExecutionImpl $instance): bool
    {
        return false;
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }

    public function execute(PvmExecutionImpl $execution): void
    {
        // calculate the propagating execution
        $propagatingExecution = $execution;

        $activity = $execution->getActivity();
        $transitionsToTake = $execution->getTransitionsToTake();
        $execution->setTransitionsToTake(null);

        // check whether the current scope needs to be destroyed
        if ($execution->isScope() && $activity->isScope()) {
            if (!LegacyBehavior::destroySecondNonScope($execution)) {
                if ($execution->isConcurrent()) {
                // legacy behavior
                    LegacyBehavior::destroyConcurrentScope($execution);
                } else {
                    $propagatingExecution = $execution->getParent();
                    //LOG.debugDestroyScope(execution, propagatingExecution);
                    $execution->destroy();
                    $propagatingExecution->setActivity($execution->getActivity());
                    $propagatingExecution->setTransition($execution->getTransition());
                    $propagatingExecution->setActive(true);
                    $execution->remove();
                }
            }
        } else {
            // activity is not scope => nothing to do
            $propagatingExecution = execution;
        }

        // take the specified transitions
        if (empty($transitionsToTake)) {
            throw new ProcessEngineException($execution->__toString() . ": No outgoing transitions from "
                . "activity " . $activity);
        } elseif (count($transitionsToTake) == 1) {
            $propagatingExecution->setTransition($transitionsToTake[0]);
            $propagatingExecution->take();
        } else {
            $propagatingExecution->inactivate();

            $outgoingExecutions = [];

            for ($i = 0; $i < count($transitionsToTake); $i += 1) {
                $transition = $transitionsToTake[$i];

                $scopeExecution = $propagatingExecution->isScope() ?
                    $propagatingExecution : $propagatingExecution->getParent();

                // reuse concurrent, propagating execution for first transition
                $concurrentExecution = null;
                if ($i == 0) {
                    $concurrentExecution = $propagatingExecution;
                } else {
                    $concurrentExecution = $scopeExecution->createConcurrentExecution();

                    if ($i == 1 && !$propagatingExecution->isConcurrent()) {
                        array_shift($outgoingExecutions);
                        // get a hold of the concurrent execution that replaced the scope propagating execution
                        $replacingExecution = null;
                        foreach ($scopeExecution->getNonEventScopeExecutions() as $concurrentChild) {
                            if (!($concurrentChild == $propagatingExecution)) {
                                $replacingExecution = $concurrentChild;
                                break;
                            }
                        }

                        $outgoingExecutions[] = new OutgoingExecution($replacingExecution, $transitionsToTake[0]);
                    }
                }

                $outgoingExecutions[] = new OutgoingExecution($concurrentExecution, $transition);
            }

            // start executions in reverse order (order will be reversed again in command context with the effect that they are
            // actually be started in correct order :) )
            $outgoingExecutions = array_reverse($outgoingExecutions);

            foreach ($outgoingExecutions as $outgoingExecution) {
                $outgoingExecution->take();
            }
        }
    }

    public function getCanonicalName(): string
    {
        return "transition-destroy-scope";
    }
}
