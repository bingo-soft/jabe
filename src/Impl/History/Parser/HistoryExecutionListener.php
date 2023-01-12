<?php

namespace Jabe\Impl\History\Parser;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExecutionListenerInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\History\Event\HistoryEvent;
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;

abstract class HistoryExecutionListener implements ExecutionListenerInterface
{
    protected $eventProducer;
    protected $historyLevel;

    public function __construct(HistoryEventProducerInterface $historyEventProducer)
    {
        $this->eventProducer = $historyEventProducer;
    }

    public function notify(/*DelegateExecutionInterface*/$execution): void
    {
        // get the event handler
        $historyEventHandler = Context::getProcessEngineConfiguration()
            ->getHistoryEventHandler();

        // delegate creation of the history event to the producer
        $historyEvent = $this->createHistoryEvent($execution);

        if ($historyEvent !== null) {
            // pass the event to the handler
            $historyEventHandler->handleEvent($historyEvent);
        }
    }

    protected function ensureHistoryLevelInitialized(): void
    {
        if ($this->historyLevel === null) {
            $this->historyLevel = Context::getProcessEngineConfiguration()->getHistoryLevel();
        }
    }

    abstract protected function createHistoryEvent(DelegateExecutionInterface $execution): ?HistoryEvent;
}
