<?php

namespace Jabe\Engine\Impl\Incident;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\IncidentInterface;

class CompositeIncidentHandler implements IncidentHandlerInterface
{
    protected $mainIncidentHandler;
    protected $incidentHandlers = [];

    /**
     * Constructor that takes a varargs parameter {@link IncidentHandler} that
     * consume the incident.
     *
     * @param mainIncidentHandler the main incident handler {@link IncidentHandler} that consume the incident and return result.
     * @param incidentHandlers    the list of {@link IncidentHandler} that consume the incident.
     */
    public function __construct(IncidentHandlerInterface $mainIncidentHandler, array $incidentHandlers)
    {
        EnsureUtil::ensureNotNull("Incident handlers", "incidentHandlers", $incidentHandlers);
        $this->initializeIncidentsHandlers($mainIncidentHandler, $incidentHandlers);
    }

    /**
     * Initialize {@link #incidentHandlers} with data transfered from constructor
     *
     * @param incidentHandlers
     */
    protected function initializeIncidentsHandlers(IncidentHandlerInterface $mainIncidentHandler, array $incidentHandlers): void
    {
        EnsureUtil::ensureNotNull("Incident handler", "mainIncidentHandler", $mainIncidentHandler);
        $this->mainIncidentHandler = $mainIncidentHandler;
        EnsureUtil::ensureNotNull("Incident handlers", "incidentHandlers", $incidentHandlers);
        foreach ($incidentHandlers as $incidentHandler) {
            $this->add($incidentHandler);
        }
    }

    /**
     * Adds the {@link IncidentHandler} to the list of
     * {@link IncidentHandler} that consume the incident.
     *
     * @param incidentHandler the {@link IncidentHandler} that consume the incident.
     */
    public function add(IncidentHandlerInterface $incidentHandler): void
    {
        EnsureUtil::ensureNotNull("Incident handler", "incidentHandler", $incidentHandler);
        $incidentHandlerType = $this->getIncidentHandlerType();
        if ($incidentHandlerType != $incidentHandler->getIncidentHandlerType()) {
            throw new ProcessEngineException(
                "Incorrect incident type handler in composite handler with type: " . $incidentHandlerType
            );
        }
        $this->incidentHandlers->add($incidentHandler);
    }

    public function getIncidentHandlerType(): string
    {
        return $this->mainIncidentHandler->getIncidentHandlerType();
    }

    public function handleIncident(IncidentContext $context, string $message): IncidentInterface
    {
        $incident = $this->mainIncidentHandler->handleIncident($context, $message);
        foreach ($this->incidentHandlers as $incidentHandler) {
            $incidentHandler->handleIncident($context, $message);
        }
        return $incident;
    }

    public function resolveIncident(IncidentContext $context): void
    {
        $this->mainIncidentHandler->resolveIncident($context);
        foreach ($this->incidentHandlers as $incidentHandler) {
            $incidentHandler->resolveIncident($context);
        }
    }

    public function deleteIncident(IncidentContext $context): void
    {
        $this->mainIncidentHandler->deleteIncident($context);
        foreach ($this->incidentHandlers as $incidentHandler) {
            $incidentHandler->deleteIncident($context);
        }
    }
}
