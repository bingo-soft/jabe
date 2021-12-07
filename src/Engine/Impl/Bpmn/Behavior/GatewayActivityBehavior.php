<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Pvm\Delegate\ActivityExecutionInterface;

abstract class GatewayActivityBehavior extends FlowNodeActivityBehavior
{
    protected function lockConcurrentRoot(ActivityExecutionInterface $execution): void
    {
        $concurrentRoot = null;
        if ($execution->isConcurrent()) {
            $concurrentRoot = $execution->getParent();
        } else {
            $concurrentRoot = $execution;
        }
        $concurrentRoot->forceUpdate();
    }
}
