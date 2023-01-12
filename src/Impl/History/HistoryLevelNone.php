<?php

namespace Jabe\Impl\History;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\History\Event\HistoryEventTypeInterface;

class HistoryLevelNone extends AbstractHistoryLevel
{
    public function getId(): int
    {
        return 0;
    }

    public function getName(): ?string
    {
        return ProcessEngineConfiguration::HISTORY_NONE;
    }

    public function isHistoryEventProduced(HistoryEventTypeInterface $eventType, $entity): bool
    {
        return false;
    }
}
