<?php

namespace Jabe\Engine\Impl\History\Parser;

use Jabe\Engine\Delegate\DelegateExecutionInterface;
use Jabe\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;

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
