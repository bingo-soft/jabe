<?php

namespace BpmPlatform\Engine\Impl\History\Parser;

use BpmPlatform\Engine\Delegate\DelegateTaskInterface;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;

class ActivityInstanceUpdateListener extends HistoryExecutionListener
{
    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        parent::__construct($historyEventProducer);
    }

    protected function createHistoryEvent(DelegateTaskInterface $task, ExecutionEntity $execution): ?HistoryEvent
    {
        $this->ensureHistoryLevelInitialized();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::activityInstanceUpdate(), $execution)) {
            return $eventProducer->createActivityInstanceUpdateEvt($execution, $task);
        } else {
            return null;
        }
    }
}
