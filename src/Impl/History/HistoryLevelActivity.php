<?php

namespace Jabe\Impl\History;

use Jabe\ProcessEngineConfiguration;
use Jabe\Impl\History\Event\{
    HistoryEventTypeInterface,
    HistoryEventTypes
};

class HistoryLevelActivity extends AbstractHistoryLevel
{
    public function getId(): int
    {
        return 1;
    }

    public function getName(): ?string
    {
        return ProcessEngineConfiguration::HISTORY_ACTIVITY;
    }

    public function isHistoryEventProduced(HistoryEventTypeInterface $eventType, $entity): bool
    {
        return HistoryEventTypes::processInstanceStart()->equals($eventType)
        || HistoryEventTypes::processInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::processInstanceMigrate()->equals($eventType)
        || HistoryEventTypes::processInstanceEnd()->equals($eventType)

        || HistoryEventTypes::taskInstanceCreate()->equals($eventType)
        || HistoryEventTypes::taskInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::taskInstanceMigrate()->equals($eventType)
        || HistoryEventTypes::taskInstanceComplete()->equals($eventType)
        || HistoryEventTypes::taskInstanceDelete()->equals($eventType)

        || HistoryEventTypes::activityInstanceStart()->equals($eventType)
        || HistoryEventTypes::activityInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::activityInstanceMigrate()->equals($eventType)
        || HistoryEventTypes::activityInstanceEnd()->equals($eventType)

        || HistoryEventTypes::caseInstanceCreate()->equals($eventType)
        || HistoryEventTypes::caseInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::caseInstanceClose()->equals($eventType)

        || HistoryEventTypes::caseActivityInstanceCreate()->equals($eventType)
        || HistoryEventTypes::caseActivityInstanceUpdate()->equals($eventType)
        || HistoryEventTypes::caseActivityInstanceEnd()->equals($eventType);
    }
}
