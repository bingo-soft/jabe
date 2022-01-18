<?php

namespace BpmPlatform\Engine\Impl\History\Parser;

use BpmPlatform\Engine\Delegate\DelegateExecutionInterface;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;

class ActivityInstanceEndListener extends HistoryExecutionListener
{
    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        parent::__construct($historyEventProducer);
    }

    protected function createHistoryEvent(DelegateExecutionInterface $execution): ?HistoryEvent
    {
        $this->ensureHistoryLevelInitialized();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceEnd(), $execution)) {
            return $eventProducer->createActivityInstanceEndEvt($execution);
        } else {
            return null;
        }
    }
}
