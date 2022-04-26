<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Delegate\ExecutionListenerInterface;
use Jabe\Engine\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Pvm\Runtime\{
    CompensationBehavior,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Core\Model\CoreModelElement;
use Jabe\Engine\Impl\Core\Instance\CoreExecution;

class PvmAtomicOperationDeleteCascadeFireActivityEnd extends PvmAtomicOperationActivityInstanceEnd
{
    protected function eventNotificationsStarted(CoreExecution $execution): CoreExecution
    {
        $execution->setCanceled(true);
        return parent::eventNotificationsStarted($execution);
    }

    protected function getScope(CoreExecution $execution): CoreModelElement
    {
        $activity = $execution->getActivity();

        if ($activity != null) {
            return $activity;
        } else {
            // TODO: when can this happen?
            $parent = $execution->getParent();
            if ($parent != null) {
                return $this->getScope($execution->getParent());
            }
            return $execution->getProcessDefinition();
        }
    }

    protected function getEventName(): string
    {
        return ExecutionListenerInterface::EVENTNAME_END;
    }

    protected function eventNotificationsCompleted(CoreExecution $execution): void
    {
        $activity = $execution->getActivity();

        if (
            $execution->isScope()
            && ($this->executesNonScopeActivity($execution) || $this->isAsyncBeforeActivity($execution))
            && !CompensationBehavior::executesNonScopeCompensationHandler($execution)
        ) {
            $execution->removeAllTasks();
            // case this is a scope execution and the activity is not a scope
            $execution->leaveActivityInstance();
            $execution->setActivity(getFlowScopeActivity($activity));
            $execution->performOperation(self::deleteCascadeFireActivityEnd());
        } else {
            if ($execution->isScope()) {
                if ($execution instanceof ExecutionEntity && !$execution->isProcessInstanceExecution() && $execution->isCanceled()) {
                    // execution was canceled and output mapping for activity is marked as skippable
                    $execution->setSkipIoMappings($execution->isSkipIoMappings() || $execution->getProcessEngine()->getProcessEngineConfiguration()->isSkipOutputMappingOnCanceledActivities());
                }
                $execution->destroy();
            }

            // remove this execution and its concurrent parent (if exists)
            $execution->remove();

            $continueRemoval = !$execution->isDeleteRoot();

            if ($continueRemoval) {
                $propagatingExecution = $execution->getParent();
                if ($propagatingExecution != null && !$propagatingExecution->isScope() && !$propagatingExecution->hasChildren()) {
                    $propagatingExecution->remove();
                    $continueRemoval = !$propagatingExecution->isDeleteRoot();
                    $propagatingExecution = $propagatingExecution->getParent();
                }

                if ($continueRemoval) {
                    if ($propagatingExecution != null) {
                        // continue deletion with the next scope execution
                        // set activity on parent in case the parent is an inactive scope execution and activity has been set to 'null'.
                        if ($propagatingExecution->getActivity() == null && $activity != null && $activity->getFlowScope() != null) {
                            $propagatingExecution->setActivity($this->getFlowScopeActivity($activity));
                        }
                    }
                }
            }
        }
    }

    protected function executesNonScopeActivity(PvmExecutionImpl $execution): bool
    {
        $activity = $execution->getActivity();
        return $activity != null && !$activity->isScope();
    }

    protected function isAsyncBeforeActivity(PvmExecutionImpl $execution): bool
    {
        return !empty($execution->getActivityId()) && empty($execution->getActivityInstanceId());
    }

    protected function getFlowScopeActivity(PvmActivityInterface $activity): ActivityImpl
    {
        $flowScope = $activity->getFlowScope();
        $flowScopeActivity = null;
        if ($flowScope->getProcessDefinition() != $flowScope) {
            $flowScopeActivity = $flowScope;
        }
        return $flowScopeActivity;
    }

    public function getCanonicalName(): string
    {
        return "delete-cascade-fire-activity-end";
    }
}
