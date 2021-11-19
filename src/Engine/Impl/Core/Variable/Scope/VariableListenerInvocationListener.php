<?php

namespace BpmPlatform\Engine\Impl\Core\Variable\Scope;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Delegate\VariableListenerInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    TaskEntity,
    VariableInstanceEntity
};

class VariableListenerInvocationListener implements VariableInstanceLifecycleListenerInterface
{
    protected $targetScope;

    public function __construct(AbstractVariableScope $targetScope)
    {
        $this->targetScope = $targetScope;
    }

    public function onCreate(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
        $this->handleEvent(new VariableEvent($variable, VariableListenerInterface::CREATE, $sourceScope));
    }

    public function onUpdate(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
        $this->handleEvent(new VariableEvent($variable, VariableListenerInterface::UPDATE, $sourceScope));
    }

    public function onDelete(VariableInstanceEntity $variable, AbstractVariableScope $sourceScope): void
    {
        $this->handleEvent(new VariableEvent($variable, VariableListenerInterface::DELETE, $sourceScope));
    }

    protected function handleEvent(VariableEvent $event): void
    {
        $sourceScope = $event->getSourceScope();
        if ($sourceScope instanceof ExecutionEntity) {
            $this->addEventToScopeExecution($sourceScope, $event);
        } elseif ($sourceScope instanceof TaskEntity) {
            $task = $sourceScope;
            $execution = $task->getExecution();
            if ($execution != null) {
                $this->addEventToScopeExecution($execution, $event);
            }
        } elseif ($sourceScope->getParentVariableScope() instanceof ExecutionEntity) {
            $this->addEventToScopeExecution($sourceScope->getParentVariableScope(), $event);
        } else {
            throw new ProcessEngineException("BPMN execution scope expected");
        }
    }

    protected function addEventToScopeExecution(ExecutionEntity $sourceScope, VariableEvent $event): void
    {
        // ignore events of variables that are not set in an execution
        $sourceExecution = $sourceScope;
        $scopeExecution = $sourceExecution->isScope() ? $sourceExecution : $sourceExecution->getParent();
        $scopeExecution->delayEvent($targetScope, $event);
    }
}
