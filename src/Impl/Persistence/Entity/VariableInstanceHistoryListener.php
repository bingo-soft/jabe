<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};
use Jabe\Impl\History\HistoryLevelInterface;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventTypes,
    HistoryEventProcessor
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;

class VariableInstanceHistoryListener implements VariableInstanceLifecycleListenerInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceLifecycleListenerInterface
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new VariableInstanceHistoryListener();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function onCreate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        if ($this->getHistoryLevel()->isHistoryEventProduced(HistoryEventTypes::variableInstanceCreate(), $variableInstance) && !$variableInstance->isTransient()) {
            HistoryEventProcessor::processHistoryEvents(new class ($variableInstance, $sourceScope) extends HistoryEventCreator {
                private $variableInstance;
                private $sourceScope;

                public function __construct(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope)
                {
                    $this->variableInstance = $variableInstance;
                    $this->sourceScope = $sourceScope;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    $evt = $producer->createHistoricVariableCreateEvt($this->variableInstance, $this->sourceScope);
                    return $evt;
                }
            });
        }
    }

    public function onDelete(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        if ($this->getHistoryLevel()->isHistoryEventProduced(HistoryEventTypes::variableInstanceDelete(), $variableInstance) && !$variableInstance->isTransient()) {
            HistoryEventProcessor::processHistoryEvents(new class ($variableInstance, $sourceScope) extends HistoryEventCreator {
                private $variableInstance;
                private $sourceScope;

                public function __construct(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope)
                {
                    $this->variableInstance = $variableInstance;
                    $this->sourceScope = $sourceScope;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricVariableDeleteEvt($this->variableInstance, $this->sourceScope);
                }
            });
        }
    }

    public function onUpdate(CoreVariableInstanceInterface $variableInstance, AbstractVariableScope $sourceScope): void
    {
        if ($this->getHistoryLevel()->isHistoryEventProduced(HistoryEventTypes::variableInstanceUpdate(), $variableInstance) && !$variableInstance->isTransient()) {
            HistoryEventProcessor::processHistoryEvents(new class ($variableInstance, $sourceScope) extends HistoryEventCreator {
                private $variableInstance;
                private $sourceScope;

                public function __construct(VariableInstanceEntity $variableInstance, ?AbstractVariableScope $sourceScope)
                {
                    $this->variableInstance = $variableInstance;
                    $this->sourceScope = $sourceScope;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    return $producer->createHistoricVariableUpdateEvt($this->variableInstance, $this->sourceScope);
                }
            });
        }
    }

    protected function getHistoryLevel(): HistoryLevelInterface
    {
        return Context::getProcessEngineConfiguration()->getHistoryLevel();
    }
}
