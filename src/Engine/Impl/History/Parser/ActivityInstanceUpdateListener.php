<?php

namespace Jabe\Engine\Impl\History\Parser;

use Jabe\Engine\Delegate\DelegateTaskInterface;
use Jabe\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;

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
