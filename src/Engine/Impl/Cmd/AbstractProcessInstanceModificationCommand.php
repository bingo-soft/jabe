<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\ActivityExecutionTreeMapping;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\CommandInterface;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Pvm\Process\{
    ProcessDefinitionImpl,
    ScopeImpl
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\{
    ActivityInstanceInterface,
    TransitionInstanceInterface
};

abstract class AbstractProcessInstanceModificationCommand implements CommandInterface
{
    public $processInstanceId;
    protected $skipCustomListeners;
    protected $skipIoMappings;
    protected $externallyTerminated;

    public function __construct(string $processInstanceId)
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function setSkipCustomListeners(bool $skipCustomListeners): void
    {
        $this->skipCustomListeners = $skipCustomListeners;
    }

    public function setSkipIoMappings(bool $skipIoMappings): void
    {
        $this->skipIoMappings = $skipIoMappings;
    }

    public function setExternallyTerminated(bool $externallyTerminated): void
    {
        $this->externallyTerminated = $externallyTerminated;
    }

    public function setProcessInstanceId(string $processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    protected function findActivityInstance(ActivityInstanceInterface $tree, string $activityInstanceId): ?ActivityInstanceInterface
    {
        if ($activityInstanceId == $tree->getId()) {
            return $tree;
        } else {
            foreach ($tree->getChildActivityInstances() as $child) {
                $matchingChildInstance = $this->findActivityInstance($child, $activityInstanceId);
                if ($matchingChildInstance !== null) {
                    return $matchingChildInstance;
                }
            }
        }
        return null;
    }

    protected function findTransitionInstance(ActivityInstanceInterface $tree, string $transitionInstanceId): ?TransitionInstanceInterface
    {
        foreach ($tree->getChildTransitionInstances() as $childTransitionInstance) {
            if ($this->matchesRequestedTransitionInstance($childTransitionInstance, $transitionInstanceId)) {
                return $childTransitionInstance;
            }
        }

        foreach ($tree->getChildActivityInstances() as $child) {
            $matchingChildInstance = $this->findTransitionInstance($child, $transitionInstanceId);
            if ($matchingChildInstance !== null) {
                return $matchingChildInstance;
            }
        }

        return null;
    }

    protected function matchesRequestedTransitionInstance(TransitionInstanceInterface $instance, string $queryInstanceId): bool
    {
        $match = $instance->getId() == $queryInstanceId;

        // check if the execution queried for has been replaced by the given instance
        // => if yes, given instance is matched
        // this is a fix for CAM-4090 to tolerate inconsistent transition instance ids as described in CAM-4143
        if (!$match) {
            // note: execution id = transition instance id
            $cachedExecution = Context::getCommandContext()
                ->getDbEntityManager()
                ->getCachedEntity(ExecutionEntity::class, $queryInstanceId);

            // follow the links of execution replacement;
            // note: this can be at most two hops:
            // case 1:
            //   the query execution is the scope execution
            //     => tree may have expanded meanwhile
            //     => scope execution references replacing execution directly (one hop)
            //
            // case 2:
            //   the query execution is a concurrent execution
            //     => tree may have compacted meanwhile
            //     => concurrent execution references scope execution directly (one hop)
            //
            // case 3:
            //   the query execution is a concurrent execution
            //     => tree may have compacted/expanded/compacted/../expanded any number of times
            //     => the concurrent execution has been removed and therefore references the scope execution (first hop)
            //     => the scope execution may have been replaced itself again with another concurrent execution (second hop)
            //   note that the scope execution may have a long "history" of replacements, but only the last replacement is relevant here
            if ($cachedExecution !== null) {
                $replacingExecution = $cachedExecution->resolveReplacedBy();
                if ($replacingExecution !== null) {
                    $match = $replacingExecution->getId() == $instance->getId();
                }
            }
        }

        return $match;
    }

    protected function getScopeForActivityInstance(
        ProcessDefinitionImpl $processDefinition,
        ActivityInstanceInterface $activityInstance
    ): ?ScopeImpl {
        $scopeId = $activityInstance->getActivityId();

        if ($processDefinition->getId() == $scopeId) {
            return $processDefinition;
        } else {
            return $processDefinition->findActivity($scopeId);
        }
    }

    protected function getScopeExecutionForActivityInstance(
        ExecutionEntity $processInstance,
        ActivityExecutionTreeMapping $mapping,
        ActivityInstanceInterface $activityInstance
    ): ExecutionEntity {
        EnsureUtil::ensureNotNull("activityInstance", $activityInstance);

        $processDefinition = $processInstance->getProcessDefinition();
        $scope = $this->getScopeForActivityInstance($processDefinition, $activityInstance);

        $executions = $mapping->getExecutions($scope);
        $activityInstanceExecutions = $activityInstance->getExecutionIds();

        // TODO: this is a hack around the activity instance tree
        // remove with fix of CAM-3574
        foreach ($activityInstance->getExecutionIds() as $activityInstanceExecutionId) {
            $execution = Context::getCommandContext()
                ->getExecutionManager()
                ->findExecutionById($activityInstanceExecutionId);
            if ($execution->isConcurrent() && $execution->hasChildren()) {
                // concurrent executions have at most one child
                $child = $execution->getExecutions()[0];
                $activityInstanceExecutions[] = $child->getId();
            }
        }

        // find the scope execution for the given activity instance
        $retainedExecutionsForInstance = [];
        foreach ($executions as $execution) {
            if (in_array($execution->getId(), $activityInstanceExecutions)) {
                $retainedExecutionsForInstance[] = $execution;
            }
        }

        if (count($retainedExecutionsForInstance) != 1) {
            throw new ProcessEngineException(
                "There are " . count($retainedExecutionsForInstance)
                . " (!= 1) executions for activity instance " .
                $activityInstance->getId()
            );
        }

        return $retainedExecutionsForInstance[0];
    }

    protected function describeFailure(string $detailMessage): string
    {
        return "Cannot perform instruction: " . $this->describe() . "; " . $detailMessage;
    }

    abstract protected function describe(): string;

    public function __toString()
    {
        return $this->describe();
    }
}
