<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Pvm\Process\{
    ProcessDefinitionImpl,
    ScopeImpl
};
use Jabe\Runtime\{
    ActivityInstanceInterface,
    TransitionInstanceInterface
};

class ActivityCancellationCmd extends AbstractProcessInstanceModificationCommand
{
    protected $activityId;
    public $cancelCurrentActiveActivityInstances;
    protected $activityInstanceTree;

    //@TODO. Check invocation arguments ordering
    public function __construct(?string $processInstanceId, ?string $activityId)
    {
        if ($processInstanceId !== null) {
            parent::__construct($processInstanceId);
        }
        $this->activityId = $activityId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $activityInstanceTree = $this->getActivityInstanceTree($commandContext);
        $commands = $this->createActivityInstanceCancellations($activityInstanceTree, $commandContext);

        foreach ($commands as $cmd) {
            $cmd->setSkipCustomListeners($this->skipCustomListeners);
            $cmd->setSkipIoMappings($this->skipIoMappings);
            $cmd->execute($commandContext);
        }

        return null;
    }

    protected function collectParentScopeIdsForActivity(ProcessDefinitionImpl $processDefinition, ?string $activityId): array
    {
        $parentScopeIds = [];
        $scope = $processDefinition->findActivity($activityId);

        while ($scope !== null) {
            $parentScopeIds[] = $scope->getId();
            $scope = $scope->getFlowScope();
        }

        return $parentScopeIds;
    }

    protected function getTransitionInstancesForActivity(ActivityInstanceInterface $tree, array $parentScopeIds): array
    {
        // prune all search paths that are not in the scope hierarchy of the activity in question
        if (!in_array($tree->getActivityId(), $parentScopeIds)) {
            return [];
        }

        $instances = [];
        $transitionInstances = $tree->getChildTransitionInstances();

        foreach ($transitionInstances as $transitionInstance) {
            if ($this->activityId == $transitionInstance->getActivityId()) {
                $instances[] = $transitionInstance;
            }
        }

        foreach ($tree->getChildActivityInstances() as $child) {
            $instances = array_merge($instances, $this->getTransitionInstancesForActivity($child, $parentScopeIds));
        }

        return $instances;
    }

    protected function getActivityInstancesForActivity(ActivityInstanceInterface $tree, array $parentScopeIds): array
    {
        // prune all search paths that are not in the scope hierarchy of the activity in question
        if (!in_array($tree->getActivityId(), $parentScopeIds)) {
            return [];
        }

        $instances = [];

        if ($this->activityId == $tree->getActivityId()) {
            $instances[] = $tree;
        }

        foreach ($tree->getChildActivityInstances() as $child) {
            $instances = array_merge($instances, $this->getActivityInstancesForActivity($child, $parentScopeIds));
        }

        return $instances;
    }

    public function getActivityInstanceTree(CommandContext $commandContext): ActivityInstanceInterface
    {
        $scope = $this;
        return $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new GetActivityInstanceCmd($scope->processInstanceId);
            return $cmd->execute($commandContext);
        });
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function setActivityInstanceTreeToCancel(ActivityInstanceInterface $activityInstanceTreeToCancel): void
    {
        $this->activityInstanceTree = $activityInstanceTreeToCancel;
    }

    protected function describe(): ?string
    {
        return "Cancel all instances of activity '" . $this->activityId . "'";
    }

    public function createActivityInstanceCancellations(ActivityInstanceInterface $activityInstanceTree, CommandContext $commandContext): array
    {
        $commands = [];

        $processInstance = $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);
        $processDefinition = $processInstance->getProcessDefinition();
        $parentScopeIds = $this->collectParentScopeIdsForActivity($processDefinition, $this->activityId);

        $childrenForActivity = $this->getActivityInstancesForActivity($activityInstanceTree, $parentScopeIds);
        foreach ($childrenForActivity as $instance) {
            $commands[] = new ActivityInstanceCancellationCmd($this->processInstanceId, $instance->getId());
        }

        $transitionInstancesForActivity = $this->getTransitionInstancesForActivity($activityInstanceTree, $parentScopeIds);
        foreach ($transitionInstancesForActivity as $instance) {
            $commands[] = new TransitionInstanceCancellationCmd($this->processInstanceId, $instance->getId());
        }
        return $commands;
    }

    public function isCancelCurrentActiveActivityInstances(): bool
    {
        return $this->cancelCurrentActiveActivityInstances;
    }

    public function setCancelCurrentActiveActivityInstances(bool $cancelCurrentActiveActivityInstances): void
    {
        $this->cancelCurrentActiveActivityInstances = $cancelCurrentActiveActivityInstances;
    }
}
