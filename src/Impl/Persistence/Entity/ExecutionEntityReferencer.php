<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Core\Variable\Scope\VariableStoreObserverInterface;

class ExecutionEntityReferencer implements VariableStoreObserverInterface
{
    protected $execution;

    public function __construct(ExecutionEntity $execution)
    {
        $this->execution = $execution;
    }

    public function onAdd(VariableInstanceEntity $variable): void
    {
        $variable->setExecution($this->execution);
    }

    public function onRemove(VariableInstanceEntity $variable): void
    {
        $variable->setExecution(null);
    }
}
