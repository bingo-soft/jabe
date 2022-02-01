<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Core\Variable\Scope\{
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
        $variableInstance->setConcurrentLocal(!$execution->isScope() || $execution->isExecutingScopeLeafActivity());
    }

    public function onDelete(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }

    public function onUpdate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }
}
