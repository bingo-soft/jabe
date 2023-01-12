<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\History\Producer\HistoryEventProducerInterface;

class HistoryEventCreator
{
    /**
     * Creates the HistoryEvent with the help off the given
     * HistoryEventProducerInterface.
     *
     * @param producer the producer which is used for the creation
     * @return HistoryEvent the created HistoryEvent
     */
    public function createHistoryEvent(HistoryEventProducerInterface $producer): ?HistoryEvent
    {
        return null;
    }

    public function createHistoryEvents(HistoryEventProducerInterface $producer): array
    {
        return [];
    }

    public function postHandleSingleHistoryEventCreated(HistoryEvent $event): void
    {
        return;
    }
}
