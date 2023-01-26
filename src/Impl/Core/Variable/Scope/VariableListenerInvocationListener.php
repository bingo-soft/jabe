<?php

namespace Jabe\Impl\Core\Variable\Scope;

use Jabe\ProcessEngineException;
use Jabe\Delegate\VariableListenerInterface;
use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;
use Jabe\Impl\Core\Variable\Event\VariableEvent;
use Jabe\Impl\Persistence\Entity\{
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

    public function onCreate(CoreVariableInstanceInterface $variable, AbstractVariableScope $sourceScope): void
    {
        $this->handleEvent(new VariableEvent($variable, VariableListenerInterface::CREATE, $sourceScope));
    }

    public function onUpdate(CoreVariableInstanceInterface $variable, AbstractVariableScope $sourceScope): void
    {
        $this->handleEvent(new VariableEvent($variable, VariableListenerInterface::UPDATE, $sourceScope));
    }

    public function onDelete(CoreVariableInstanceInterface $variable, AbstractVariableScope $sourceScope): void
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
            if ($execution !== null) {
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
        $scopeExecution->delayEvent($this->targetScope, $event);
    }
}
