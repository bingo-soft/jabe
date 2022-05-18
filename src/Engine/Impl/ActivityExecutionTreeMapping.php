<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\Process\{
    ProcessDefinitionImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Pvm\Runtime\{
    CompensationBehavior,
    PvmExecutionImpl
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\ActivityInstanceInterface;

class ActivityExecutionTreeMapping
{
    protected $activityExecutionMapping = [];
    protected $commandContext;
    protected $processInstanceId;
    protected $processDefinition;

    public function __construct(CommandContext $commandContext, string $processInstanceId)
    {
        $this->commandContext = $commandContext;
        $this->processInstanceId = $processInstanceId;
        $this->initialize();
    }

    protected function submitExecution(ExecutionEntity $execution, ScopeImpl $scope): void
    {
        $this->addExecution($execution, $scope);
    }

    public function addExecution(ExecutionEntity $execution, ScopeImpl $scope): void
    {
        foreach ($this->activityExecutionMapping as $key => $pair) {
            if ($pair[0] == $scope) {
                $this->activityExecutionMapping[$key][] = $execution;
                return;
            }
        }
        $this->activityExecutionMapping[] = [$scope, $execution];
    }

    public function getExecutions(ScopeImpl $activity): array
    {
        foreach ($this->activityExecutionMapping as $key => $pair) {
            if ($pair[0] == $activity) {
                return array_slice($pair, 1);
            }
        }
        $this->activityExecutionMapping[] = [$activity];
        return [];
    }

    public function getExecution(ActivityInstance $activityInstance): ExecutionEntity
    {
        $scope = null;

        if ($activityInstance->getId() == $activityInstance->getProcessInstanceId()) {
            $scope = $this->processDefinition;
        } else {
            $scope = $this->processDefinition->findActivity($activityInstance->getActivityId());
        }

        return $this->intersect(
            $this->getExecutions($scope),
            $activityInstance->getExecutionIds()
        );
    }

    protected function intersect(array $executions, array $executionIds): ?ExecutionEntity
    {
        $executionIdSet = [];
        foreach ($executionIds as $executionId) {
            $executionIdSet[] = $executionId;
        }

        foreach ($executions as $execution) {
            if (in_array($execution->getId(), $executionIdSet)) {
                return $execution;
            }
        }
        throw new ProcessEngineException("Could not determine execution");
    }

    protected function initialize(): void
    {
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);
        $this->processDefinition = $processInstance->getProcessDefinition();

        $executions = $this->fetchExecutionsForProcessInstance($processInstance);
        $executions[] = $processInstance;

        $leaves = $this->findLeaves($executions);

        $this->assignExecutionsToActivities($leaves);
    }

    protected function assignExecutionsToActivities(array $leaves): void
    {
        foreach ($leaves as $leaf) {
            $activity = $leaf->getActivity();
            if ($activity != null) {
                if ($leaf->getActivityInstanceId() != null) {
                    EnsureUtil::ensureNotNull("activity", "activity", $activity);
                    $this->submitExecution($leaf, $activity);
                }
                $this->mergeScopeExecutions($leaf);
            } elseif ($leaf->isProcessInstanceExecution()) {
                $this->submitExecution($leaf, $leaf->getProcessDefinition());
            }
        }
    }

    protected function mergeScopeExecutions(ExecutionEntity $leaf): void
    {
        $mapping = $leaf->createActivityExecutionMapping();

        foreach ($mapping as $pair) {
            $scope = $pair[0];
            $scopeExecution = $pair[1];

            $this->submitExecution($scopeExecution, $scope);
        }
    }

    protected function fetchExecutionsForProcessInstance(ExecutionEntity $execution): array
    {
        $executions = $execution->getExecutions();
        foreach ($execution->getExecutions() as $child) {
            $executions = array_merge($executions, $this->fetchExecutionsForProcessInstance($child));
        }
        return $executions;
    }

    protected function findLeaves(array $executions): array
    {
        $leaves = [];

        foreach ($executions as $execution) {
            if ($this->isLeaf($execution)) {
                $leaves[] = $execution;
            }
        }

        return $leaves;
    }

    /**
     * event-scope executions are not considered in this mapping and must be ignored
     */
    protected function isLeaf(ExecutionEntity $execution): bool
    {
        if (CompensationBehavior::isCompensationThrowing($execution)) {
            return true;
        } else {
            return !$execution->isEventScope() && empty($execution->getNonEventScopeExecutions());
        }
    }
}
