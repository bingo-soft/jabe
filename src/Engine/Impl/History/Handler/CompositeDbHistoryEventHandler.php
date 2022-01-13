<?php

namespace BpmPlatform\Engine\Impl\History\Handler;

class CompositeDbHistoryEventHandler extends CompositeHistoryEventHandler
{
    /**
     * Constructor that takes a varargs parameter {@link HistoryEventHandler} that
     * consume the event and adds {@link DbHistoryEventHandler} to the list of
     * {@link HistoryEventHandler}.
     *
     * @param historyEventHandlers
     *          the list of {@link HistoryEventHandler} that consume the event.
     */
    public function __construct(?array $historyEventHandlers = [])
    {
        parent::__construct($historyEventHandlers);
        $this->addDefaultDbHistoryEventHandler();
    }

    /**
     * Add {@link DbHistoryEventHandler} to the list of
     * {@link HistoryEventHandler}.
     */
    private function addDefaultDbHistoryEventHandler(): void
    {
        $this->historyEventHandlers[] = new DbHistoryEventHandler();
    }
}
