<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Management\DeploymentStatisticsQueryInterface;

class DeploymentStatisticsQueryImpl extends AbstractQuery implements DeploymentStatisticsQueryInterface
{
    protected $includeFailedJobs = false;
    protected $includeIncidents = false;
    protected $includeIncidentsForType;

    // for internal use
    protected $processInstancePermissionChecks = [];
    protected $jobPermissionChecks = [];
    protected $incidentPermissionChecks = [];

    public function __construct(CommandExecutorInterface $executor)
    {
        parent::__construct($executor);
    }

    public function includeFailedJobs(): DeploymentStatisticsQueryInterface
    {
        $this->includeFailedJobs = true;
        return $this;
    }

    public function includeIncidents(): DeploymentStatisticsQueryInterface
    {
        $this->includeIncidents = true;
        return $this;
    }

    public function includeIncidentsForType(string $incidentType): DeploymentStatisticsQueryInterface
    {
        $this->includeIncidentsForType = $incidentType;
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return
            $commandContext
            ->getStatisticsManager()
            ->getStatisticsCountGroupedByDeployment($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return
            $commandContext
            ->getStatisticsManager()
            ->getStatisticsGroupedByDeployment($this, $page);
    }

    public function isFailedJobsToInclude(): bool
    {
        return $this->includeFailedJobs;
    }

    public function isIncidentsToInclude(): bool
    {
        return $this->includeIncidents || $this->includeIncidentsForType !== null;
    }

    protected function checkQueryOk(): void
    {
        parent::checkQueryOk();
        if ($this->includeIncidents && $this->includeIncidentsForType !== null) {
            throw new ProcessEngineException("Invalid query: It is not possible to use includeIncident() and includeIncidentForType() to execute one query.");
        }
    }

    // getter/setter for authorization check

    public function getProcessInstancePermissionChecks(): array
    {
        return $this->processInstancePermissionChecks;
    }

    public function setProcessInstancePermissionChecks(array $processInstancePermissionChecks): void
    {
        $this->processInstancePermissionChecks = $processInstancePermissionChecks;
    }

    public function addProcessInstancePermissionCheck(array $permissionChecks): void
    {
        $this->processInstancePermissionChecks = array_merge($this->processInstancePermissionChecks, $permissionChecks);
    }

    public function getJobPermissionChecks(): array
    {
        return $this->jobPermissionChecks;
    }

    public function setJobPermissionChecks(array $jobPermissionChecks): void
    {
        $this->jobPermissionChecks = $jobPermissionChecks;
    }

    public function addJobPermissionCheck(array $permissionChecks): void
    {
        $this->jobPermissionChecks = array_merge($this->jobPermissionChecks, $permissionChecks);
    }

    public function getIncidentPermissionChecks(): array
    {
        return $this->incidentPermissionChecks;
    }

    public function setIncidentPermissionChecks(array $incidentPermissionChecks): void
    {
        $this->incidentPermissionChecks = $incidentPermissionChecks;
    }

    public function addIncidentPermissionCheck(array $permissionChecks): void
    {
        $this->incidentPermissionChecks = array_merge($this->incidentPermissionChecks, $permissionChecks);
    }
}
