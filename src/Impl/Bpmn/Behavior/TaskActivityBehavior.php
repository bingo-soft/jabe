<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class TaskActivityBehavior extends AbstractBpmnActivityBehavior
{
    /**
     * Activity instance id before execution.
     */
    protected $activityInstanceId;

    /**
     * The method which will be called before the execution is performed.
     *
     * @param execution the execution which is used during execution
     * @throws Exception
     */
    protected function preExecution(ActivityExecutionInterface $execution): void
    {
        $this->activityInstanceId = $execution->getActivityInstanceId();
    }

    /**
     * The method which should be overridden by the sub classes to perform an execution.
     *
     * @param execution the execution which is used during performing the execution
     * @throws Exception
     */
    protected function performExecution(ActivityExecutionInterface $execution): void
    {
        $this->leave($execution);
    }

    /**
     * The method which will be called after performing the execution.
     *
     * @param execution the execution
     * @throws Exception
     */
    protected function postExecution(ActivityExecutionInterface $execution): void
    {
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        $this->performExecution($execution);
    }
}
