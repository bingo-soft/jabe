<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Management\{
    ProcessDefinitionStatisticsInterface,
    ProcessDefinitionStatisticsQueryInterface
};

class ProcessDefinitionStatisticsQueryImpl extends AbstractQuery implements ProcessDefinitionStatisticsQueryInterface
{
    protected bool $includeFailedJobs = false;
    protected bool $includeIncidents = false;
    protected bool $includeRootIncidents = false;
    protected $includeIncidentsForType;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return
        $commandContext
            ->getStatisticsManager()
            ->getStatisticsCountGroupedByProcessDefinitionVersion($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return
        $commandContext
            ->getStatisticsManager()
            ->getStatisticsGroupedByProcessDefinitionVersion($this, $page);
    }

    public function includeFailedJobs(): ProcessDefinitionStatisticsQueryInterface
    {
        $this->includeFailedJobs = true;
        return $this;
    }

    public function includeIncidents(): ProcessDefinitionStatisticsQueryInterface
    {
        $this->includeIncidents = true;
        return $this;
    }

    public function includeIncidentsForType(?string $incidentType): ProcessDefinitionStatisticsQueryInterface
    {
        $this->includeIncidentsForType = $incidentType;
        return $this;
    }

    public function isFailedJobsToInclude(): bool
    {
        return $this->includeFailedJobs;
    }

    public function isIncidentsToInclude(): bool
    {
        return $this->includeIncidents || $this->includeRootIncidents || $this->includeIncidentsForType !== null;
    }

    protected function checkQueryOk(): void
    {
        parent::checkQueryOk();
        if ($this->includeIncidents && $this->includeIncidentsForType !== null) {
            throw new ProcessEngineException("Invalid query: It is not possible to use includeIncident() and includeIncidentForType() to execute one query.");
        }
        if ($this->includeRootIncidents && $this->includeIncidentsForType !== null) {
            throw new ProcessEngineException("Invalid query: It is not possible to use includeRootIncident() and includeIncidentForType() to execute one query.");
        }
        if ($this->includeIncidents && $this->includeRootIncidents) {
            throw new ProcessEngineException("Invalid query: It is not possible to use includeIncident() and includeRootIncidents() to execute one query.");
        }
    }

    public function includeRootIncidents(): ProcessDefinitionStatisticsQueryInterface
    {
        $this->includeRootIncidents = true;
        return $this;
    }
}
