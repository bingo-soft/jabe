<?php

namespace Jabe\Impl\History\Handler;

class CompositeDbHistoryEventHandler extends CompositeHistoryEventHandler
{
    /**
     * Constructor that takes a varargs parameter HistoryEventHandler that
     * consume the event and adds DbHistoryEventHandler to the list of
     * HistoryEventHandler.
     *
     * @param historyEventHandlers
     *          the list of HistoryEventHandler that consume the event.
     */
    public function __construct(?array $historyEventHandlers = [])
    {
        parent::__construct($historyEventHandlers);
        $this->addDefaultDbHistoryEventHandler();
    }

    /**
     * Add DbHistoryEventHandler to the list of
     * HistoryEventHandler.
     */
    private function addDefaultDbHistoryEventHandler(): void
    {
        $this->historyEventHandlers[] = new DbHistoryEventHandler();
    }
}
