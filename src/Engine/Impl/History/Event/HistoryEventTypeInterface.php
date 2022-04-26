<?php

namespace Jabe\Engine\Impl\History\Event;

interface HistoryEventTypeInterface extends \Serializable
{
    /**
     * The type of the entity.
     */
    public function getEntityType(): string;

    /**
     * The name of the event fired on the entity
     */
    public function getEventName(): string;
}
