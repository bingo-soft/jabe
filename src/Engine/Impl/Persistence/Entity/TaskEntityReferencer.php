<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Core\Variable\Scope\VariableStoreObserverInterface;

class TaskEntityReferencer implements VariableStoreObserverInterface
{
    protected $task;

    public function __construct(TaskEntity $task)
    {
        $this->task = $task;
    }

    public function onAdd(VariableInstanceEntity $variable): void
    {
        $variable->setTask($this->task);
    }

    public function onRemove(VariableInstanceEntity $variable): void
    {
        $variable->setTask(null);
    }
}
