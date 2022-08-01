<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\Delegate\ModificationObserverBehaviorInterface;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};

class ModificationUtil
{
    public static function handleChildRemovalInScope(ExecutionEntity $removedExecution): void
    {
        $activity = $removedExecution->getActivity();
        if ($activity === null) {
            if ($removedExecution->getSuperExecution() !== null) {
                $removedExecution = $removedExecution->getSuperExecution();
                $activity = $removedExecution->getActivity();
                if ($activity == null) {
                    return;
                }
            } else {
                return;
            }
        }
        $flowScope = $activity->getFlowScope();

        $scopeExecution = $removedExecution->getParentScopeExecution(false);
        $executionInParentScope = $removedExecution->isConcurrent() ? $removedExecution : $removedExecution->getParent();

        if ($flowScope->getActivityBehavior() != null && $flowScope->getActivityBehavior() instanceof ModificationObserverBehaviorInterface) {
            // let child removal be handled by the scope itself
            $behavior = $flowScope->getActivityBehavior();
            $behavior->destroyInnerInstance($executionInParentScope);
        } else {
            if ($executionInParentScope->isConcurrent()) {
                $executionInParentScope->remove();
                $scopeExecution->tryPruneLastConcurrentChild();
                $scopeExecution->forceUpdate();
            }
        }
    }
}
