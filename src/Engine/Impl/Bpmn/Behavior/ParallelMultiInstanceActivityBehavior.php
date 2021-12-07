<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Migration\Instance\MigratingActivityInstance;
use BpmPlatform\Engine\Impl\Migration\Instance\Parser\MigratingInstanceParseContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    VariableInstanceEntity
};
use BpmPlatform\Engine\Impl\Pvm\PvmActivityInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    MigrationObserverBehavior
};
use BpmPlatform\Engine\Impl\Pvm\Process\ActivityImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
    CallbackInterface,
    PvmExecutionImpl
};

class ParallelMultiInstanceActivityBehavior extends MultiInstanceActivityBehavior implements MigrationObserverBehaviorInterface
{
    protected function createInstances(ActivityExecutionInterface $execution, int $nrOfInstances): void
    {
        $innerActivity = $this->getInnerActivity($execution->getActivity());

        // initialize the scope and create the desired number of child executions
        $this->prepareScopeExecution($execution, $nrOfInstances);

        $concurrentExecutions = [];
        for ($i = 0; $i < $nrOfInstances; $i += 1) {
            $concurrentExecutions[] = $this->createConcurrentExecution($execution);
        }

        // start the concurrent child executions
        // start executions in reverse order (order will be reversed again in command context with the effect that they are
        // actually be started in correct order :) )
        for ($i = ($nrOfInstances - 1); $i >= 0; $i -= 1) {
            $activityExecution = $concurrentExecutions[$i];
            $this->performInstance($activityExecution, $innerActivity, $i);
        }
    }

    protected function prepareScopeExecution(ActivityExecutionInterface $scopeExecution, int $nrOfInstances): void
    {
        // set the MI-body scoped variables
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES, $nrOfInstances);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES, 0);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfInstances);
        $scopeExecution->setActivity(null);
        $scopeExecution->inactivate();
    }

    protected function createConcurrentExecution(ActivityExecutionInterface $scopeExecution): ActivityExecutionInterface
    {
        $concurrentChild = $scopeExecution->createExecution();
        $scopeExecution->createConcurrentExecutionforceUpdate();
        $concurrentChild->setConcurrent(true);
        $concurrentChild->setScope(false);
        return $concurrentChild;
    }

    public function concurrentChildExecutionEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): void
    {
        $nrOfCompletedInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES) + 1;
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES, $nrOfCompletedInstances);
        $nrOfActiveInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES) - 1;
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfActiveInstances);

        // inactivate the concurrent execution
        $endedExecution->inactivate();
        $endedExecution->setActivityInstanceId(null);

        // join
        $scopeExecution->forceUpdate();
        // TODO: should the completion condition be evaluated on the scopeExecution or on the endedExecution?
        if (
            $this->completionConditionSatisfied($endedExecution) ||
            $this->allExecutionsEnded($scopeExecution, $endedExecution)
        ) {
            $childExecutions = $scopeExecution->getNonEventScopeExecutions();
            foreach ($childExecutions as $$childExecution) {
                // delete all not-ended instances; these are either active (for non-scope tasks) or inactive but have no activity id (for subprocesses, etc.)
                if ($childExecution->isActive() || $childExecution->getActivity() == null) {
                    $childExecution->deleteCascade("Multi instance completion condition satisfied.");
                } else {
                    $childExecution->remove();
                }
            }

            $scopeExecution->setActivity($endedExecution->getActivity()->getFlowScope());
            $scopeExecution->setActive(true);
            $this->leave($activityExecutionscopeExecution);
        } else {
            $scopeExecution->dispatchDelayedEventsAndPerformOperation(new class () implements CallbackInterface {
                public function callback($execution)
                {
                }
            });
        }
    }

    protected function allExecutionsEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): bool
    {
        $numberOfInactiveConcurrentExecutions = count($endedExecution->findInactiveConcurrentExecutions($endedExecution->getActivity()));
        $concurrentExecutions = count($scopeExecution->getExecutions());

        // no active instances exist and all concurrent executions are inactive
        return $this->getLocalLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES) <= 0 &&
                $numberOfInactiveConcurrentExecutions == $concurrentExecutions;
    }

    public function complete(ActivityExecutionInterface $scopeExecution): void
    {
      // can't happen
    }

    public function initializeScope(ActivityExecutionInterface $scopeExecution, int $numberOfInstances): array
    {
        $this->prepareScopeExecution($scopeExecution, $numberOfInstances);

        $executions = [];
        for ($i = 0; $i < $numberOfInstances; $i += 1) {
            $concurrentChild = $this->createConcurrentExecution($scopeExecution);
            $this->setLoopVariable($concurrentChild, self::LOOP_COUNTER, $i);
            $executions[]  = $concurrentChild;
        }

        return $executions;
    }

    public function createInnerInstance(ActivityExecutionInterface $scopeExecution): ActivityExecutionInterface
    {
        // even though there is only one instance, there is always a concurrent child
        $concurrentChild = $this->createConcurrentExecution($scopeExecution);

        $nrOfInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES, $nrOfInstances + 1);
        $nrOfActiveInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfActiveInstances + 1);

        $this->setLoopVariable($concurrentChild, self::LOOP_COUNTER, $nrOfInstances);

        return $concurrentChild;
    }

    public function destroyInnerInstance(ActivityExecutionInterface $concurrentExecution): void
    {
        $scopeExecution = $concurrentExecution->getParent();
        $concurrentExecution->remove();
        $scopeExecution->forceUpdate();

        $nrOfActiveInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfActiveInstances - 1);
    }

    public function migrateScope(ActivityExecutionInterface $scopeExecution): void
    {
        // migrate already completed instances
        foreach ($scopeExecution->getExecutions() as $$child) {
            if (!$child->isActive()) {
                $child->setProcessDefinition($scopeExecution->getProcessDefinition());
            }
        }
    }

    public function onParseMigratingInstance(MigratingInstanceParseContext $parseContext, MigratingActivityInstance $migratingInstance): void
    {
        $scopeExecution = $migratingInstance->resolveRepresentativeExecution();

        $concurrentInActiveExecutions =
            $scopeExecution->findInactiveChildExecutions($this->getInnerActivity($migratingInstance->getSourceScope()));

        // variables on ended inner instance executions need not be migrated anywhere
        // since they are also not represented in the tree of migrating instances, we remove
        // them from the parse context here to avoid a validation exception
        foreach ($concurrentInActiveExecutions as $execution) {
            foreach ($execution->getVariablesInternal() as $variable) {
                $parseContext->consume($variable);
            }
        }
    }
}
