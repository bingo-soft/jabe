<?php

namespace Jabe\Engine\Impl\History\Handler;

use Jabe\Engine\Impl\History\Event\HistoryEvent;

interface HistoryEventHandlerInterface
{
    /**
     * Called by the process engine when an history event is fired.
     *
     * @param historyEvent the {@link HistoryEvent} that is about to be fired.
     */
    public function handleEvent(HistoryEvent $historyEvent): void;

    /**
     * Called by the process engine when an history event is fired.
     *
     * @param historyEvents the {@link HistoryEvent} that is about to be fired.
     */
    public function handleEvents(array $historyEvents): void;
}
