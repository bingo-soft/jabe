<?php

namespace Jabe\Impl\History\Parser;

use Jabe\Delegate\DelegateExecutionInterface;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;

class ActivityInstanceStartListener extends HistoryExecutionListener
{
    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        parent::__construct($historyEventProducer);
    }

    protected function createHistoryEvent(DelegateExecutionInterface $execution): ?HistoryEvent
    {
        $this->ensureHistoryLevelInitialized();
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceStart(), $execution)) {
            $evt = $this->eventProducer->createActivityInstanceStartEvt($execution);
            return $evt;
        } else {
            return null;
        }
    }
}
