<?php

namespace Jabe\Engine\Impl\History\Event;

use Jabe\Engine\History\IncidentStateImpl;

class HistoricIncidentEventEntity extends HistoryEvent
{
    protected $createTime;
    protected $endTime;
    protected $incidentType;
    protected $activityId;
    protected $causeIncidentId;
    protected $rootCauseIncidentId;
    protected $configuration;
    protected $incidentMessage;
    protected $incidentState;
    protected $tenantId;
    protected $jobDefinitionId;
    protected $historyConfiguration;
    protected $failedActivityId;
    protected $annotation;

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getIncidentType(): string
    {
        return $this->incidentType;
    }

    public function setIncidentType(string $incidentType): void
    {
        $this->incidentType = $incidentType;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getCauseIncidentId(): string
    {
        return $this->causeIncidentId;
    }

    public function setCauseIncidentId(string $causeIncidentId): void
    {
        $this->causeIncidentId = $causeIncidentId;
    }

    public function getRootCauseIncidentId(): string
    {
        return $this->rootCauseIncidentId;
    }

    public function setRootCauseIncidentId(string $rootCauseIncidentId): void
    {
        $this->rootCauseIncidentId = $rootCauseIncidentId;
    }

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getHistoryConfiguration(): string
    {
        return $this->historyConfiguration;
    }

    public function setHistoryConfiguration(string $historyConfiguration): void
    {
        $this->historyConfiguration = $historyConfiguration;
    }

    public function getIncidentMessage(): string
    {
        return $this->incidentMessage;
    }

    public function setIncidentMessage(string $incidentMessage): void
    {
        $this->incidentMessage = $incidentMessage;
    }

    public function setIncidentState(int $incidentState): void
    {
        $this->incidentState = $incidentState;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function isOpen(): bool
    {
        return IncidentStateImpl::default()->getStateCode() == $this->incidentState;
    }

    public function isDeleted(): bool
    {
        return IncidentStateImpl::deleted()->getStateCode() == $this->incidentState;
    }

    public function isResolved(): bool
    {
        return IncidentStateImpl::resolved()->getStateCode() == $this->incidentState;
    }

    public function getRootProcessInstanceId(): string
    {
        return $this->rootProcessInstanceId;
    }

    public function setRootProcessInstanceId(string $rootProcessInstanceId): void
    {
        $this->rootProcessInstanceId = $rootProcessInstanceId;
    }

    public function getFailedActivityId(): ?string
    {
        return $this->failedActivityId;
    }

    public function setFailedActivityId(string $failedActivityId): void
    {
        $this->failedActivityId = $failedActivityId;
    }

    public function getAnnotation(): string
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): void
    {
        $this->annotation = $annotation;
    }
}
