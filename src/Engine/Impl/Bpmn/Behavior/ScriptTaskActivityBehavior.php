<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Behavior;

use BpmPlatform\Engine\Delegate\BpmnError;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Delegate\ScriptInvocation;
use BpmPlatform\Engine\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface
};
use BpmPlatform\Engine\Impl\Scripting\ExecutableScript;

class ScriptTaskActivityBehavior extends TaskActivityBehavior
{
    protected $script;
    protected $resultVariable;

    public function __construct(ExecutableScript $script, string $resultVariable)
    {
        $this->script = $script;
        $this->resultVariable = $resultVariable;
    }

    public function performExecution(ActivityExecutionInterface $execution): void
    {
        $scope = $this;
        $this->executeWithErrorPropagation($execution, function () use ($scope, $execution) {
            $invocation = new ScriptInvocation($scope->script, $execution);
            Context::getProcessEngineConfiguration()->getDelegateInterceptor()->handleInvocation($invocation);
            $result = $invocation->getInvocationResult();
            if ($result != null && $resultVariable != null) {
                $execution->setVariable($resultVariable, $result);
            }
            $scope->leave($execution);
            return null;
        });
    }

    /**
     * Searches recursively through the exception to see if the exception itself
     * or one of its causes is a {@link BpmnError}.
     *
     * @param e
     *          the exception to check
     * @return the BpmnError that was the cause of this exception or null if no
     *         BpmnError was found
     */
    protected function checkIfCauseOfExceptionIsBpmnError(\Throwable $e): BpmnError
    {
        if ($e instanceof BpmnError) {
            return $e;
        } elseif (!method_exists($e, 'getCause') || $e->getCause() == null) {
            return null;
        }
        return $this->checkIfCauseOfExceptionIsBpmnError($e->getCause());
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }
}
