<?php

namespace BpmPlatform\Engine\Impl\History\Parser;

use BpmPlatform\Engine\Delegate\DelegateExecutionInterface;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;

class ProcessInstanceUpdateListener extends HistoryExecutionListener
{
    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        parent::__construct($historyEventProducer);
    }

    protected function createHistoryEvent(DelegateExecutionInterface $execution): HistoryEvent
    {
        $this->ensureHistoryLevelInitialized();
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceUpdate(), $execution)) {
            return $this->eventProducer->createProcessInstanceUpdateEvt($execution);
        } else {
            return null;
        }
    }
}
