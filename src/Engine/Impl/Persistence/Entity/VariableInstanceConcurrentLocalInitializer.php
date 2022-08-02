<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};

class VariableInstanceConcurrentLocalInitializer implements VariableInstanceLifecycleListenerInterface
{
    protected $execution;

    public function __construct(ExecutionEntity $execution)
    {
        $this->execution = $execution;
    }

    public function onCreate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->setConcurrentLocal(!$this->execution->isScope() || $this->execution->isExecutingScopeLeafActivity());
    }

    public function onDelete(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }

    public function onUpdate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }
}
