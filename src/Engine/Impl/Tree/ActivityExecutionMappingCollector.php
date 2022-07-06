<?php

namespace Jabe\Engine\Impl\Tree;

use Jabe\Engine\Impl\Pvm\PvmScopeInterface;
use Jabe\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;
use Jabe\Engine\Impl\Pvm\Process\ScopeImpl;
use Jabe\Engine\Impl\Pvm\Runtime\{
    LegacyBehavior,
    PvmExecutionImpl
};

class ActivityExecutionMappingCollector implements TreeVisitorInterface
{
    private $activityExecutionMapping = [];

    private $initialExecution;
    private $initialized = false;

    public function __construct(ActivityExecutionInterface $execution)
    {
        $this->initialExecution = $execution;
    }

    public function visit(/*ActivityExecutionInterface */$execution): void
    {
        if (!$this->initialized) {
            // lazy initialization to avoid exceptions on creation
            $this->appendActivityExecutionMapping($this->initialExecution);
            $this->initialized = true;
        }

        $this->appendActivityExecutionMapping($execution);
    }

    private function appendActivityExecutionMapping(ActivityExecutionInterface $execution): void
    {
        if ($execution->getActivity() !== null && !LegacyBehavior::hasInvalidIntermediaryActivityId($execution)) {
            $this->activityExecutionMapping = array_merge($this->activityExecutionMapping, $execution->createActivityExecutionMapping());
        }
    }

    /**
     * @return PvmExecutionImpl the mapped execution for scope or <code>null</code>, if no mapping exists
     */
    public function getExecutionForScope(PvmScopeInterface $scope): ?PvmExecutionImpl
    {
        foreach ($this->activityExecutionMapping as $pair) {
            if ($pair[0] == $scope) {
                return $pair[1];
            }
        }
        return null;
    }
}
