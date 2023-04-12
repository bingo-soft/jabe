<?php

namespace Jabe\Impl\Delegate;

use Jabe\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Scripting\ExecutableScript;

class ScriptInvocation extends DelegateInvocation
{
    protected $script;
    protected $scope;

    public function __construct(ExecutableScript $script, ?VariableScopeInterface $scope, ?BaseDelegateExecutionInterface $contextExecution)
    {
        parent::__construct($contextExecution, null);
        $this->script = $script;
        $this->scope = $scope;
    }

    protected function invoke(): void
    {
        $this->invocationResult = Context::getProcessEngineConfiguration()
            ->getScriptingEnvironment()
            ->execute($this->script, $this->scope);
    }
}
