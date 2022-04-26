<?php

namespace Jabe\Engine\Impl\History\Parser;

use Jabe\Engine\Delegate\{
    DelegateTaskInterface,
    TaskListenerInterface
};
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\History\HistoryLevel;
use Jabe\Engine\Impl\History\Event\HistoryEvent;
use Jabe\Engine\Impl\History\Handler\HistoryEventHandlerInterface;
use Jabe\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    TaskEntity
};

abstract class HistoryTaskListener implements TaskListenerInterface
{
    protected $eventProducer;
    protected $historyLevel;

    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        $this->eventProducer = $historyEventProducer;
    }

    public function notify(DelegateTaskInterface $task): void
    {
        // get the event handler
        $historyEventHandler = Context::getProcessEngineConfiguration()
            ->getHistoryEventHandler();

        $execution = $task->getExecution();

        if ($execution != null) {
            // delegate creation of the history event to the producer
            $historyEvent = $this->createHistoryEvent($task, $execution);
            if ($historyEvent != null) {
                // pass the event to the handler
                $historyEventHandler->handleEvent($historyEvent);
            }
        }
    }

    protected function ensureHistoryLevelInitialized(): void
    {
        if ($this->historyLevel == null) {
            $this->historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        }
    }

    abstract protected function createHistoryEvent(DelegateTaskInterface $task, ExecutionEntityInterface $execution): HistoryEvent;
}
