<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\ProcessEngineException;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface
};
use Jabe\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    CompositeActivityBehaviorInterface
};
use Jabe\Impl\Pvm\Runtime\{
    LegacyBehavior,
    PvmExecutionImpl
};

class PvmAtomicOperationActivityEnd implements PvmAtomicOperationInterface
{
    use BasePvmAtomicOperationTrait;

    protected function getScope(PvmExecutionImpl $execution): PvmScopeInterface
    {
        return $execution->getActivity();
    }

    public function isAsync(PvmExecutionImpl $execution): bool
    {
        return $execution->getActivity()->isAsyncAfter();
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }

    public function execute(PvmExecutionImpl $execution, ...$args): void
    {
        // restore activity instance id
        if (empty($execution->getActivityInstanceId())) {
            $execution->setActivityInstanceId($execution->getParentActivityInstanceId());
        }

        $activity = $execution->getActivity();
        $activityExecutionMapping = $execution->createActivityExecutionMapping();

        $propagatingExecution = $execution;

        if ($execution->isScope() && $activity->isScope()) {
            if (!LegacyBehavior::destroySecondNonScope($execution)) {
                $execution->destroy();
                if (!$execution->isConcurrent()) {
                    $execution->remove();
                    $propagatingExecution = $execution->getParent();
                    $propagatingExecution->setActivity($execution->getActivity());
                }
            }
        }

        $propagatingExecution = LegacyBehavior::determinePropagatingExecutionOnEnd($propagatingExecution, $activityExecutionMapping);
        $flowScope = $activity->getFlowScope();

        // 1. flow scope = Process Definition
        if ($flowScope == $activity->getProcessDefinition()) {
            // 1.1 concurrent execution => end + tryPrune()
            if ($propagatingExecution->isConcurrent()) {
                $propagatingExecution->remove();
                $propagatingExecution->getParent()->tryPruneLastConcurrentChild();
                $propagatingExecution->getParent()->forceUpdate();
            } else {
                // 1.2 Process End
                $propagatingExecution->setEnded(true);
                if (!$propagatingExecution->isPreserveScope()) {
                    $propagatingExecution->performOperation(self::processEnd());
                }
            }
        } else {
            // 2. flowScope != process definition
            $flowScopeActivity = $flowScope;

            $activityBehavior = $flowScopeActivity->getActivityBehavior();
            if ($activityBehavior instanceof CompositeActivityBehaviorInterface) {
                $compositeActivityBehavior = $activityBehavior;
                // 2.1 Concurrent execution => composite behavior.concurrentExecutionEnded()
                if ($propagatingExecution->isConcurrent() && !LegacyBehavior::isConcurrentScope($propagatingExecution)) {
                    $compositeActivityBehavior->concurrentChildExecutionEnded($propagatingExecution->getParent(), $propagatingExecution);
                } else {
                    // 2.2 Scope Execution => composite behavior.complete()
                    $propagatingExecution->setActivity($flowScopeActivity);
                    $compositeActivityBehavior->complete($propagatingExecution);
                }
            } else {
                // activity behavior is not composite => this is unexpected
                throw new ProcessEngineException(
                    "Expected behavior of composite scope " . $activity .
                    " to be a CompositeActivityBehavior"
                );
            }
        }
    }

    public function getCanonicalName(): ?string
    {
        return "activity-end";
    }
}
