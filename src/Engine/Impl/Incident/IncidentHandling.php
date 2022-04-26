<?php

namespace Jabe\Engine\Impl\Incident;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Runtime\IncidentInterface;

class IncidentHandling
{
    public static function createIncident(
        string $incidentType,
        IncidentContext $context,
        string $message
    ): IncidentInterface {
        $handler = Context::getProcessEngineConfiguration()
            ->getIncidentHandler($incidentType);

        if ($handler == null) {
            $handler = new DefaultIncidentHandler($incidentType);
        }

        return $handler->handleIncident($context, $message);
    }

    public static function removeIncidents(
        string $incidentType,
        IncidentContext $context,
        bool $incidentsResolved
    ): void {
        $handler = Context::getProcessEngineConfiguration()
            ->getIncidentHandler($incidentType);

        if ($handler == null) {
            $handler = new DefaultIncidentHandler($incidentType);
        }

        if ($incidentsResolved) {
            $handler->resolveIncident($context);
        } else {
            $handler->deleteIncident($context);
        }
    }
}
