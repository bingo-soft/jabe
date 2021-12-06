<?php

namespace BpmPlatform\Engine\Impl\Delegate;

use BpmPlatform\Engine\Delegate\{
    BaseDelegateExecutionInterface,
    VariableScopeInterface
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Scripting\ExecutableScript;

class ScriptInvocation extends DelegateInvocation
{
    protected $script;
    protected $scope;

    public function __construct(ExecutableScript $script, VariableScopeInterface $scope, ?BaseDelegateExecutionInterface $contextExecution)
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
