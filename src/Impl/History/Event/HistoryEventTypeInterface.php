<?php

namespace Jabe\Impl\History\Event;

interface HistoryEventTypeInterface
{
    /**
     * The type of the entity.
     */
    public function getEntityType(): ?string;

    /**
     * The name of the event fired on the entity
     */
    public function getEventName(): ?string;
}
