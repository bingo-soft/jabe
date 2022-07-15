<?php

namespace Jabe\Engine\Impl\Incident;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\IncidentEntity;
use Jabe\Engine\Runtime\IncidentInterface;

class DefaultIncidentHandler implements IncidentHandlerInterface
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getIncidentHandlerType(): string
    {
        return $this->type;
    }

    public function handleIncident(IncidentContext $context, string $message): IncidentInterface
    {
        return $this->createIncident($context, $message);
    }

    public function createIncident(IncidentContext $context, string $message): IncidentInterface
    {
        $newIncident = IncidentEntity::createAndInsertIncident($this->type, $context, $message);

        if ($context->getExecutionId() !== null) {
            $newIncident->createRecursiveIncidents();
        }

        return $newIncident;
    }

    public function resolveIncident(IncidentContext $context): void
    {
        $this->removeIncident($context, true);
    }

    public function deleteIncident(IncidentContext $context): void
    {
        $this->removeIncident($context, false);
    }

    protected function removeIncident(IncidentContext $context, bool $incidentResolved): void
    {
        $incidents = Context::getCommandContext()
            ->getIncidentManager()
            ->findIncidentByConfiguration($context->getConfiguration());

        foreach ($incidents as $currentIncident) {
            $incident = $currentIncident;
            if ($incidentResolved) {
                $incident->resolve();
            } else {
                $incident->delete();
            }
        }
    }
}
