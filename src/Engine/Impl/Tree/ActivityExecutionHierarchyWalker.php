<?php

namespace BpmPlatform\Engine\Impl\Tree;

use BpmPlatform\Engine\Impl\Pvm\PvmScopeInterface;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\ScopeImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class ActivityExecutionHierarchyWalker extends SingleReferenceWalker
{
    private $activityExecutionMapping;

    public function __construct(ActivityExecutionInterface $execution)
    {
        parent::__construct(self::createTuple($execution));
        $this->activityExecutionMapping = $execution->createActivityExecutionMapping();
    }

    protected function nextElement()
    {
        $currentElement = $this->getCurrentElement();

        $currentScope = $currentElement->getScope();
        $currentExecution = $currentElement->getExecution();

        $flowScope = $currentScope->getFlowScope();

        if (!$currentExecution->isScope()) {
            foreach ($this->activityExecutionMapping as $pair) {
                if ($pair[0] == $currentScope) {
                    return new ActivityExecutionTuple($currentScope, $pair[1]);
                }
            }
            return null;
        } elseif ($flowScope != null) {
            // walk to parent scope
            foreach ($this->activityExecutionMapping as $pair) {
                if ($pair[0] == $flowScope) {
                    return new ActivityExecutionTuple(flowScope, $pair[1]);
                }
            }
            return null;
        } else {
            // this is the process instance, look for parent
            foreach ($this->activityExecutionMapping as $pair) {
                if ($pair[0] == $currentScope) {
                    $currentExecution = $pair[1];
                }
            }
            $superExecution = $currentExecution->getSuperExecution();

            if ($superExecution != null) {
                // walk to parent process instance
                $this->activityExecutionMapping = $superExecution->createActivityExecutionMapping();
                return self::createTuple($superExecution);
            } else {
                // this is the top level process instance
                return null;
            }
        }
    }

    protected static function createTuple(ActivityExecutionInterface $execution): ActivityExecutionTuple
    {
        $flowScope = self::getCurrentFlowScope($execution);
        return new ActivityExecutionTuple($flowScope, $execution);
    }

    protected static function getCurrentFlowScope(ActivityExecutionInterface $execution): ?PvmScopeInterface
    {
        $scope = null;
        if ($execution->getTransition() != null) {
            $scope = $execution->getTransition()->getDestination()->getFlowScope();
        } else {
            $scope = $execution->getActivity();
        }

        if ($scope->isScope()) {
            return $scope;
        } else {
            return $scope->getFlowScope();
        }
    }

    public function addScopePreVisitor(TreeVisitorInterface $visitor): ReferenceWalker
    {
        return $this->addPreVisitor(new ScopeVisitorWrapper($visitor));
    }

    public function addScopePostVisitor(TreeVisitorInterface $visitor): ReferenceWalker
    {
        return $this->addPostVisitor(new ScopeVisitorWrapper($visitor));
    }

    public function addExecutionPreVisitor(TreeVisitorInterface $visitor): ReferenceWalker
    {
        return $this->addPreVisitor(new ExecutionVisitorWrapper($visitor));
    }

    public function addExecutionPostVisitor(TreeVisitorInterface $visitor): ReferenceWalker
    {
        return $this->addPostVisitor(new ExecutionVisitorWrapper($visitor));
    }
}
