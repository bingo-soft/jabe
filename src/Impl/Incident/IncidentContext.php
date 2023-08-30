<?php

namespace Jabe\Impl\Incident;

use Jabe\Runtime\IncidentInterface;

class IncidentContext
{
    protected $processDefinitionId;
    protected $activityId;
    protected $executionId;
    protected $configuration;
    protected $tenantId;
    protected $jobDefinitionId;
    protected $historyConfiguration;
    protected $failedActivityId;

    public function __construct(?IncidentInterface $incident = null)
    {
        if ($incident !== null) {
            $this->processDefinitionId = $incident->getProcessDefinitionId();
            $this->activityId = $incident->getActivityId();
            $this->executionId = $incident->getExecutionId();
            $this->configuration = $incident->getConfiguration();
            $this->tenantId = $incident->getTenantId();
            $this->jobDefinitionId = $incident->getJobDefinitionId();
        }
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function setProcessDefinitionId(?string $processDefinitionId): void
    {
        $this->processDefinitionId = $processDefinitionId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function setActivityId(?string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function setExecutionId(?string $executionId): void
    {
        $this->executionId = $executionId;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function setConfiguration(?string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getJobDefinitionId(): ?string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(?string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getHistoryConfiguration(): ?string
    {
        return $this->historyConfiguration;
    }

    public function setHistoryConfiguration(?string $historicConfiguration): void
    {
        $this->historyConfiguration = $historicConfiguration;
    }

    public function getFailedActivityId(): ?string
    {
        return $this->failedActivityId;
    }

    public function setFailedActivityId(?string $failedActivityId): void
    {
        $this->failedActivityId = $failedActivityId;
    }
}
