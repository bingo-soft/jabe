<?php

namespace Jabe\Engine\Impl\History\Event;

class HistoryEventCreator
{
    /**
     * Creates the HistoryEvent with the help off the given
     * HistoryEventProducer.
     *
     * @param producer the producer which is used for the creation
     * @return HistoryEvent the created HistoryEvent
     */
    public function createHistoryEvent(HistoryEventProducer $producer): ?HistoryEvent
    {
        return null;
    }

    public function createHistoryEvents(HistoryEventProducer $producer): array
    {
        return [];
    }

    public function postHandleSingleHistoryEventCreated(HistoryEvent $event): void
    {
        return;
    }
}
