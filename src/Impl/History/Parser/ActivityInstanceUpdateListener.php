<?php

namespace Jabe\Impl\History\Parser;

use Jabe\Delegate\DelegateTaskInterface;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;

class ActivityInstanceUpdateListener extends HistoryExecutionListener
{
    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        parent::__construct($historyEventProducer);
    }

    protected function createHistoryEvent(DelegateTaskInterface $task, ExecutionEntity $execution): ?HistoryEvent
    {
        $this->ensureHistoryLevelInitialized();
        if ($this->historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceUpdate(), $execution)) {
            return $this->eventProducer->createActivityInstanceUpdateEvt($execution, $task);
        } else {
            return null;
        }
    }
}
