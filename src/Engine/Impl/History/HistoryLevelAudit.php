<?php

namespace BpmPlatform\Engine\Impl\History;

use BpmPlatform\Engine\ProcessEngineConfiguration;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEventTypeInterface,
    HistoryEventTypes
};

class HistoryLevelAudit extends HistoryLevelActivity
{
    public function getId(): int
    {
        return 2;
    }

    public function getName(): string
    {
        return ProcessEngineConfiguration::HISTORY_AUDIT;
    }

    public function isHistoryEventProduced(HistoryEventTypeInterface $eventType, $entity): bool
    {
        return parent::isHistoryEventProduced($eventType, $entity)
        || HistoryEventTypes::variableInstanceCreate()->equals($eventType)
        || HistoryEventTypes::variableInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::variableInstanceMigrate()->equals($eventType)
        || HistoryEventTypes::variableInstanceDelete()->equals($eventType);
    }
}
