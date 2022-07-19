<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\History\{
    HistoricJobLog,
    HistoricJobLogQueryInterface,
    JobStateImpl,
    JobStateInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\{
    CollectionUtil,
    CompareUtil,
    EnsureUtil
};

class HistoricJobLogQueryImpl extends AbstractQuery implements HistoricJobLogQueryInterface
{
    protected $id;
    protected $jobId;
    protected $jobExceptionMessage;
    protected $jobDefinitionId;
    protected $jobDefinitionType;
    protected $jobDefinitionConfiguration;
    protected $activityIds = [];
    protected $failedActivityIds = [];
    protected $executionIds = [];
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $deploymentId;
    protected $state;
    protected $jobPriorityHigherThanOrEqual;
    protected $jobPriorityLowerThanOrEqual;
    protected $tenantIds = [];
    protected $isTenantIdSet;
    protected $hostname;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function logId(string $historicJobLogId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "historicJobLogId", $historicJobLogId);
        $this->id = $historicJobLogId;
        return $this;
    }

    public function jobId(string $jobId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "jobId", $jobId);
        $this->jobId = $jobId;
        return $this;
    }

    public function jobExceptionMessage(string $jobExceptionMessage): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "jobExceptionMessage", $jobExceptionMessage);
        $this->jobExceptionMessage = $jobExceptionMessage;
        return $this;
    }

    public function jobDefinitionId(string $jobDefinitionId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "jobDefinitionId", $jobDefinitionId);
        $this->jobDefinitionId = $jobDefinitionId;
        return $this;
    }

    public function jobDefinitionType(string $jobDefinitionType): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "jobDefinitionType", $jobDefinitionType);
        $this->jobDefinitionType = $jobDefinitionType;
        return $this;
    }

    public function jobDefinitionConfiguration(string $jobDefinitionConfiguration): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "jobDefinitionConfiguration", $jobDefinitionConfiguration);
        $this->jobDefinitionConfiguration = $jobDefinitionConfiguration;
        return $this;
    }

    public function activityIdIn(array $activityIds): HistoricJobLogQueryInterface
    {
        $activityIdList = CollectionUtil::asArrayList($activityIds);
        EnsureUtil::ensureNotContainsNull("activityIds cannot contain null", "activityIds", $activityIdList);
        EnsureUtil::ensureNotContainsEmptyString("activityIds cannot contain empty string", "activityIds", $activityIdList);
        $this->activityIds = $activityIds;
        return $this;
    }

    public function failedActivityIdIn(array $activityIds): HistoricJobLogQueryInterface
    {
        $activityIdList = CollectionUtil::asArrayList($activityIds);
        EnsureUtil::ensureNotContainsNull("activityIds cannot contain null", "activityIds", $activityIdList);
        EnsureUtil::ensureNotContainsEmptyString("activityIds cannot contain empty string", "activityIds", $activityIdList);
        $this->failedActivityIds = $activityIds;
        return $this;
    }

    public function executionIdIn(array $executionIds): HistoricJobLogQueryInterface
    {
        $executionIdList = CollectionUtil::asArrayList($executionIds);
        EnsureUtil::ensureNotContainsNull("executionIds cannot contain null", "executionIds", $executionIdList);
        EnsureUtil::ensureNotContainsEmptyString("executionIds cannot contain empty string", "executionIds", $executionIdList);
        $this->executionIds = $executionIds;
        return $this;
    }

    public function processInstanceId(string $processInstanceId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function deploymentId(string $deploymentId): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);
        $this->deploymentId = $deploymentId;
        return $this;
    }

    public function jobPriorityHigherThanOrEquals(int $priority): HistoricJobLogQueryInterface
    {
        $this->jobPriorityHigherThanOrEqual = $priority;
        return $this;
    }

    public function jobPriorityLowerThanOrEquals(int $priority): HistoricJobLogQueryInterface
    {
        $this->jobPriorityLowerThanOrEqual = $priority;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricJobLogQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function hostname(string $hostname): HistoricJobLogQueryInterface
    {
        EnsureUtil::ensureNotEmpty("hostName", "hostname", $hostname);
        $this->hostname = $hostname;
        return $this;
    }

    public function creationLog(): HistoricJobLogQueryInterface
    {
        $this->setState(JobStateImpl::created());
        return $this;
    }

    public function failureLog(): HistoricJobLogQueryInterface
    {
        $this->setState(JobStateImpl::failed());
        return $this;
    }

    public function successLog(): HistoricJobLogQueryInterface
    {
        $this->setState(JobStateImpl::successful());
        return $this;
    }

    public function deletionLog(): HistoricJobLogQueryInterface
    {
        $this->setState(JobStateImpl::deleted());
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
        || CompareUtil::areNotInAscendingOrder($this->jobPriorityHigherThanOrEqual, $this->jobPriorityLowerThanOrEqual);
    }

    public function orderByTimestamp(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::timestamp());
        return $this;
    }

    public function orderByJobId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::jobId());
        return $this;
    }

    public function orderByJobDueDate(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryPropert::duedate());
        return $this;
    }

    public function orderByJobRetries(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::retries());
        return $this;
    }

    public function orderByJobPriority(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::priority());
        return $this;
    }

    public function orderByJobDefinitionId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::jobDefinitionId());
        return $this;
    }

    public function orderByActivityId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::activityId());
        return $this;
    }

    public function orderByExecutionId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::executionId());
        return $this;
    }

    public function orderByProcessInstanceId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByProcessDefinitionKey(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::processDefinitionKey());
        return $this;
    }

    public function orderByDeploymentId(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::deploymentId());
        return $this;
    }

    public function orderPartiallyByOccurrence(): HistoricJobLogQueryInterface
    {
        $this->orderBy(HistoricJobLogQueryProperty::sequenceCounter());
        return $this;
    }

    public function orderByTenantId(): HistoricJobLogQueryInterface
    {
        return orderBy(HistoricJobLogQueryProperty::tenantId());
    }

    public function orderByHostname(): HistoricJobLogQueryInterface
    {
        return orderBy(HistoricJobLogQueryProperty::hostname());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricJobLogManager()
            ->findHistoricJobLogsCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricJobLogManager()
            ->findHistoricJobLogsByQueryCriteria($this, $page);
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getJobExceptionMessage(): string
    {
        return $this->jobExceptionMessage;
    }

    public function getJobDefinitionId(): string
    {
        return $this->jobDefinitionId;
    }

    public function getJobDefinitionType(): string
    {
        return $this->jobDefinitionType;
    }

    public function getJobDefinitionConfiguration(): string
    {
        return $this->jobDefinitionConfiguration;
    }

    public function getActivityIds(): array
    {
        return $this->activityIds;
    }

    public function getFailedActivityIds(): array
    {
        return $this->failedActivityIds;
    }

    public function getExecutionIds(): array
    {
        return $this->executionIds;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function getState(): JobStateInterface
    {
        return $this->state;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    protected function setState(JobStateInterface $state): void
    {
        $this->state = $state;
    }
}
