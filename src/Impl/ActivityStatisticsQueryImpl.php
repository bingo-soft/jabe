<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Management\ActivityStatisticsQueryInterface;

class ActivityStatisticsQueryImpl extends AbstractQuery implements ActivityStatisticsQueryInterface
{
    protected $includeFailedJobs = false;
    protected $processDefinitionId;
    protected $includeIncidents;
    protected $includeIncidentsForType;

    // for internal use
    protected $processInstancePermissionChecks = [];
    protected $jobPermissionChecks = [];
    protected $incidentPermissionChecks = [];

    public function __construct(string $processDefinitionId, CommandExecutorInterface $executor)
    {
        parent::__construct($executor);
        $this->processDefinitionId = $processDefinitionId;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return
        $commandContext
            ->getStatisticsManager()
            ->getStatisticsCountGroupedByActivity($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return
        $commandContext
            ->getStatisticsManager()
            ->getStatisticsGroupedByActivity($this, $page);
    }

    public function includeFailedJobs(): ActivityStatisticsQueryInterface
    {
        $this->includeFailedJobs = true;
        return $this;
    }

    public function includeIncidents(): ActivityStatisticsQueryInterface
    {
        $this->includeIncidents = true;
        return $this;
    }

    public function includeIncidentsForType(string $incidentType): ActivityStatisticsQueryInterface
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
        return $this->includeIncidents || $this->includeIncidentsForType !== null;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    protected function checkQueryOk(): void
    {
        parent::checkQueryOk();
        EnsureUtil::ensureNotNull("No valid process definition id supplied", "processDefinitionId", $this->processDefinitionId);
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
