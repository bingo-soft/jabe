<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};
use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;

class VariableInstanceConcurrentLocalInitializer implements VariableInstanceLifecycleListenerInterface
{
    protected $execution;

    public function __construct(ExecutionEntity $execution)
    {
        $this->execution = $execution;
    }

    public function onCreate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        $variableInstance->setConcurrentLocal(!$this->execution->isScope() || $this->execution->isExecutingScopeLeafActivity());
    }

    public function onDelete(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }

    public function onUpdate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
    }
}
