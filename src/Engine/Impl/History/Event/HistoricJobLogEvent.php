<?php

namespace BpmPlatform\Engine\Impl\History\Event;

use BpmPlatform\Engine\History\JobStateImpl;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Persistence\Entity\ByteArrayEntity;
use BpmPlatform\Engine\Impl\Util\{
    ExceptionUtil,
    StringUtil
};

class HistoricJobLogEvent extends HistoryEvent
{
    protected $timestamp;

    protected $jobId;

    protected $jobDueDate;

    protected $jobRetries;

    protected $jobPriority;

    protected $jobExceptionMessage;

    protected $exceptionByteArrayId;

    protected $jobDefinitionId;

    protected $jobDefinitionType;

    protected $jobDefinitionConfiguration;

    protected $activityId;

    protected $failedActivityId;

    protected $deploymentId;

    protected $state;

    protected $tenantId;

    protected $hostname;

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function getJobDueDate(): string
    {
        return $this->jobDueDate;
    }

    public function setJobDueDate(string $jobDueDate): void
    {
        $this->jobDueDate = $jobDueDate;
    }

    public function getJobRetries(): int
    {
        return $this->jobRetries;
    }

    public function setJobRetries(int $jobRetries): void
    {
        $this->jobRetries = $jobRetries;
    }

    public function getJobPriority(): int
    {
        return $this->jobPriority;
    }

    public function setJobPriority(int $jobPriority): void
    {
        $this->jobPriority = $jobPriority;
    }

    public function getJobExceptionMessage(): string
    {
        return $this->jobExceptionMessage;
    }

    public function setJobExceptionMessage(string $jobExceptionMessage): void
    {
        // note: it is not a clean way to truncate where the history event is produced, since truncation is only
        //   relevant for relational history databases that follow our schema restrictions;
        //   a similar problem exists in JobEntity#setExceptionMessage where truncation may not be required for custom
        //   persistence implementations
        $this->jobExceptionMessage = StringUtil::trimToMaximumLengthAllowed($jobExceptionMessage);
    }

    public function getExceptionByteArrayId(): string
    {
        return $this->exceptionByteArrayId;
    }

    public function setExceptionByteArrayId(string $exceptionByteArrayId): void
    {
        $this->exceptionByteArrayId = $exceptionByteArrayId;
    }

    public function getExceptionStacktrace(): string
    {
        $byteArray = $this->getExceptionByteArray();
        return ExceptionUtil::getExceptionStacktrace($byteArray);
    }

    protected function getExceptionByteArray(): ?ByteArrayEntity
    {
        if ($this->exceptionByteArrayId != null) {
            return Context::getCommandContext()
            ->getDbEntityManager()
            ->selectById(ByteArrayEntity::class, $this->exceptionByteArrayId);
        }

        return null;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function setJobDefinitionId(string $jobDefinitionId): void
    {
        $this->jobDefinitionId = $jobDefinitionId;
    }

    public function getJobDefinitionType(): string
    {
        return $this->jobDefinitionType;
    }

    public function setJobDefinitionType(string $jobDefinitionType): void
    {
        $this->jobDefinitionType = $jobDefinitionType;
    }

    public function getJobDefinitionConfiguration(): string
    {
        return $this->jobDefinitionConfiguration;
    }

    public function setJobDefinitionConfiguration(string $jobDefinitionConfiguration): void
    {
        $this->jobDefinitionConfiguration = $jobDefinitionConfiguration;
    }

    public function getActivityId(): string
    {
        return $this->activityId;
    }

    public function setActivityId(string $activityId): void
    {
        $this->activityId = $activityId;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): void
    {
        $this->hostname = $hostname;
    }

    public function isCreationLog(): bool
    {
        return $this->state == JobStateImpl::created()->getStateCode();
    }

    public function isFailureLog(): bool
    {
        return $this->state == JobStateImpl::failed()->getStateCode();
    }

    public function isSuccessLog(): bool
    {
        return $this->state == JobStateImpl::successful()->getStateCode();
    }

    public function isDeletionLog(): bool
    {
        return $this->state == JobStateImpl::deleted()->getStateCode();
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
}
