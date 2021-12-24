<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Core\Variable\Scope\VariableStoreObserverInterface;

class ExecutionEntityReferencer implements VariableStoreObserverInterface
{
    protected $execution;

    public function __construct(ExecutionEntity $execution)
    {
        $this->execution = $execution;
    }

    public function onAdd(VariableInstanceEntity $variable): void
    {
        $variable->setExecution($execution);
    }

    public function onRemove(VariableInstanceEntity $variable): void
    {
        $variable->setExecution(null);
    }
}
