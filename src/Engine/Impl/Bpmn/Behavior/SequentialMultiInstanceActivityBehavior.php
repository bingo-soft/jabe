<?php

namespace Jabe\Engine\Impl\Bpmn\Behavior;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

class SequentialMultiInstanceActivityBehavior extends MultiInstanceActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected function createInstances(ActivityExecutionInterface $execution, int $nrOfInstances): void
    {
        $this->prepareScope($execution, $nrOfInstances);
        $this->setLoopVariable($execution, self::NUMBER_OF_ACTIVE_INSTANCES, 1);

        $innerActivity = $this->getInnerActivity($execution->getActivity());
        $this->performInstance($execution, $innerActivity, 0);
    }

    public function complete(ActivityExecutionInterface $scopeExecution): void
    {
        $loopCounter = $this->getLoopVariable($scopeExecution, self::LOOP_COUNTER) + 1;
        $nrOfInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES);
        $nrOfCompletedInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES) + 1;

        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES, $nrOfCompletedInstances);

        if ($loopCounter == nrOfInstances || $this->completionConditionSatisfied($scopeExecution)) {
            $this->leave($scopeExecution);
        } else {
            $innerActivity = $this->getInnerActivity($scopeExecution->getActivity());
            $this->performInstance($scopeExecution, $innerActivity, $loopCounter);
        }
    }

    public function concurrentChildExecutionEnded(ActivityExecutionInterface $scopeExecution, ActivityExecutionInterface $endedExecution): void
    {
        // cannot happen
    }

    protected function prepareScope(ActivityExecutionInterface $scopeExecution, int $totalNumberOfInstances): void
    {
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES, $totalNumberOfInstances);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_COMPLETED_INSTANCES, 0);
    }

    public function initializeScope(ActivityExecutionInterface $scopeExecution, int $nrOfInstances): array
    {
        if ($nrOfInstances > 1) {
            //LOG.unsupportedConcurrencyException(scopeExecution.toString(), this.getClass().getSimpleName());
        }

        $executions = [];

        $this->prepareScope($scopeExecution, $nrOfInstances);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfInstances);

        if ($nrOfInstances > 0) {
            $this->setLoopVariable($scopeExecution, self::LOOP_COUNTER, 0);
            $executions[] = $scopeExecution;
        }

        return $executions;
    }

    public function createInnerInstance(ActivityExecutionInterface $scopeExecution): ActivityExecutionInterface
    {
        if ($this->hasLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES) && $this->getLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES) > 0) {
            //throw LOG.unsupportedConcurrencyException(scopeExecution.toString(), this.getClass().getSimpleName());
        } else {
            $nrOfInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES);

            $this->setLoopVariable($scopeExecution, self::LOOP_COUNTER, $nrOfInstances);
            $this->setLoopVariable($scopeExecution, self::NUMBER_OF_INSTANCES, $nrOfInstances + 1);
            $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, 1);
        }

        return $scopeExecution;
    }

    public function destroyInnerInstance(ActivityExecutionInterface $scopeExecution): void
    {
        $this->removeLoopVariable($scopeExecution, self::LOOP_COUNTER);

        $nrOfActiveInstances = $this->getLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES);
        $this->setLoopVariable($scopeExecution, self::NUMBER_OF_ACTIVE_INSTANCES, $nrOfActiveInstances - 1);
    }
}
