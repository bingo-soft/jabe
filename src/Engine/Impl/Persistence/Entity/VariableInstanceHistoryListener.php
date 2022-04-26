<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Variable\Scope\{
    AbstractVariableScope,
    VariableInstanceLifecycleListenerInterface
};
use Jabe\Engine\Impl\History\AbstractHistoryLevel;
use Jabe\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;

class VariableInstanceHistoryListener implements VariableInstanceLifecycleListenerInterface
{
    private static $INSTANCE;

    public static function instance(): VariableInstanceLifecycleListenerInterface
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new VariableInstanceHistoryListener();
        }
        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function onCreate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
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
                    return $producer->createHistoricVariableCreateEvt($this->variableInstance, $this->sourceScope);
                }
            });
        }
    }

    public function onDelete(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
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

    public function onUpdate(VariableInstanceEntity $variableInstance, AbstractVariableScope $sourceScope): void
    {
        if ($this->getHistoryLevel()->isHistoryEventProduced(HistoryEventTypes::variableInstanceUpdate(), $variableInstance) && !$variableInstance->isTransient()) {
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
                    return $producer->createHistoricVariableUpdateEvt($this->variableInstance, $this->sourceScope);
                }
            });
        }
    }

    protected function getHistoryLevel(): AbstractHistoryLevel
    {
        return Context::getProcessEngineConfiguration()->getHistoryLevel();
    }
}
