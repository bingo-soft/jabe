<?php

namespace Jabe\Engine\Impl\History;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Impl\History\Event\HistoryEventTypeInterface;

class HistoryLevelFull extends AbstractHistoryLevel
{
    public function getId(): int
    {
        return 3;
    }

    public function getName(): string
    {
        return ProcessEngineConfiguration::HISTORY_FULL;
    }

    public function isHistoryEventProduced(HistoryEventTypeInterface $eventType, $entity): bool
    {
        return true;
    }
}
