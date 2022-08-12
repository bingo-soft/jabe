<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Context\Context;

class HistoryEventProcessor
{
    /**
     * Process an HistoryEvent and handle them directly after creation.
     * The HistoryEvent is created with the help of the given
     * HistoryEventCreator implementation.
     *
     * @param creator the creator is used to create the HistoryEvent which should be thrown
     */
    public static function processHistoryEvents(HistoryEventCreator $creator): void
    {
        $historyEventProducer = Context::getProcessEngineConfiguration()->getHistoryEventProducer();
        $historyEventHandler = Context::getProcessEngineConfiguration()->getHistoryEventHandler();

        $singleEvent = $creator->createHistoryEvent($historyEventProducer);
        if ($singleEvent !== null) {
            $historyEventHandler->handleEvent($singleEvent);
            $creator->postHandleSingleHistoryEventCreated($singleEvent);
        }

        $eventList = $creator->createHistoryEvents($historyEventProducer);
        $historyEventHandler->handleEvents($eventList);
    }
}
