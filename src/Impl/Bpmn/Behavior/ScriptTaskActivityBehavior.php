<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Delegate\BpmnError;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ScriptInvocation;
use Jabe\Impl\Pvm\Delegate\{
    ActivityBehaviorInterface,
    ActivityExecutionInterface
};
use Jabe\Impl\Scripting\ExecutableScript;

class ScriptTaskActivityBehavior extends TaskActivityBehavior
{
    protected $script;
    protected $resultVariable;

    public function __construct(ExecutableScript $script, ?string $resultVariable)
    {
        parent::__construct();
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
            if ($result !== null && $this->resultVariable !== null) {
                $execution->setVariable($this->resultVariable, $result);
            }
            $scope->leave($execution);
            return null;
        });
    }

    /**
     * Searches recursively through the exception to see if the exception itself
     * or one of its causes is a BpmnError.
     *
     * @param e
     *          the exception to check
     * @return BpmnError the BpmnError that was the cause of this exception or null if no
     *         BpmnError was found
     */
    protected function checkIfCauseOfExceptionIsBpmnError(\Throwable $e): BpmnError
    {
        if ($e instanceof BpmnError) {
            return $e;
        } elseif (!method_exists($e, 'getCause') || $e->getCause() === null) {
            return null;
        }
        return $this->checkIfCauseOfExceptionIsBpmnError($e->getCause());
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }
}
