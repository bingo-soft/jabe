<?php

namespace Jabe\Engine\Impl\Incident;

use Jabe\Engine\Runtime\IncidentInterface;

interface IncidentHandlerInterface
{
    /**
     * Returns the incident type this handler activates for.
     */
    public function getIncidentHandlerType(): string;

    /**
     * Handle an incident that arose in the context of an execution.
     */
    public function handleIncident(IncidentContext $context, string $message): IncidentInterface;

    /**
     * Called in situations in which an incident handler may wish to resolve existing incidents
     * The implementation receives this callback to enable it to resolve any open incidents that
     * may exist.
     */
    public function resolveIncident(IncidentContext $context): void;

    /**
     * Called in situations in which an incident handler may wish to delete existing incidents
     * Example: when a scope is ended or a job is deleted. The implementation receives
     * this callback to enable it to delete any open incidents that may exist.
     */
    public function deleteIncident(IncidentContext $context): void;
}
