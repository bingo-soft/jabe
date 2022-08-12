<?php

namespace Jabe\Impl\Scripting;

use Jabe\{
    ProcessEngineException,
    ScriptEvaluationException
};
use Jabe\Delegate\{
    DelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Impl\ConditionInterface;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Delegate\ScriptInvocation;

class ScriptCondition implements ConditionInterface
{
    protected $script;

    public function __construct(ExecutableScript $script)
    {
        $this->script = $script;
    }

    public function evaluate(?VariableScopeInterface $scope, DelegateExecutionInterface $execution): bool
    {
        if ($scope === null) {
            $scope = $execution;
        }
        $invocation = new ScriptInvocation($this->script, $scope, $execution);
        try {
            Context::getProcessEngineConfiguration()
            ->getDelegateInterceptor()
            ->handleInvocation($invocation);
        } catch (\Exception $e) {
            throw new ProcessEngineException($e->getMessage());
        }

        $result = $invocation->getInvocationResult();

        return $result;
    }

    public function tryEvaluate(?VariableScopeInterface $scope, DelegateExecutionInterface $execution): bool
    {
        $result = false;

        $result = $this->evaluate($scope, $execution);

        return $result;
    }

    public function getScript(): ExecutableScript
    {
        return $this->script;
    }
}
