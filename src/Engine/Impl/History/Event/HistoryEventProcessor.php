<?php

namespace BpmPlatform\Engine\Impl\History\Event;

use BpmPlatform\Engine\ProcessEngineConfiguration;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\History\Handler\HistoryEventHandlerInterface;
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;

class HistoryEventProcessor
{
    /**
     * Process an {@link HistoryEvent} and handle them directly after creation.
     * The {@link HistoryEvent} is created with the help of the given
     * {@link HistoryEventCreator} implementation.
     *
     * @param creator the creator is used to create the {@link HistoryEvent} which should be thrown
     */
    public static function processHistoryEvents(HistoryEventCreator $creator): void
    {
        $historyEventProducer = Context::getProcessEngineConfiguration()->getHistoryEventProducer();
        $historyEventHandler = Context::getProcessEngineConfiguration()->getHistoryEventHandler();

        $singleEvent = $creator->createHistoryEvent($historyEventProducer);
        if ($singleEvent != null) {
            $historyEventHandler->handleEvent($singleEvent);
            $creator->postHandleSingleHistoryEventCreated($singleEvent);
        }

        $eventList = $creator->createHistoryEvents($historyEventProducer);
        $historyEventHandler->handleEvents($eventList);
    }
}
