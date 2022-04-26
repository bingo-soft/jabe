<?php

namespace Jabe\Engine\Impl\History\Parser;

use Jabe\Engine\Delegate\DelegateExecutionInterface;
use Jabe\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;

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
