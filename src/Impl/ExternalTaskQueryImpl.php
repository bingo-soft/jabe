<?php

namespace Jabe\Impl;

use Jabe\ExternalTask\ExternalTaskQueryInterface;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\{
    ClockUtil,
    CompareUtil,
    EnsureUtil//,
    //ImmutablePair
};

class ExternalTaskQueryImpl extends AbstractQuery implements ExternalTaskQueryInterface
{
    protected $externalTaskId;
    protected $externalTaskIds = [];
    protected $workerId;
    protected $lockExpirationBefore;
    protected $lockExpirationAfter;
    protected $topicName;
    protected $locked;
    protected $notLocked;
    protected $executionId;
    protected $processInstanceId;
    protected $processInstanceIdIn = [];
    protected $processDefinitionId;
    protected $activityId;
    protected $activityIdIn = [];
    protected $suspensionState;
    protected $priorityHigherThanOrEquals;
    protected $priorityLowerThanOrEquals;
    protected $retriesLeft;
    protected $tenantIds = [];

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function externalTaskId(?string $externalTaskId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("externalTaskId", "externalTaskId", $externalTaskId);
        $this->externalTaskId = $externalTaskId;
        return $this;
    }

    public function externalTaskIdIn(array $externalTaskIds): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Set of external task ids", "externalTaskIds", $externalTaskIds);
        $this->externalTaskIds = $externalTaskIds;
        return $this;
    }

    public function workerId(?string $workerId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("workerId", "workerId", $workerId);
        $this->workerId = $workerId;
        return $this;
    }

    public function lockExpirationBefore(?string $lockExpirationDate): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("lockExpirationBefore", "lockExpirationDate", $lockExpirationDate);
        $this->lockExpirationBefore = $lockExpirationDate;
        return $this;
    }

    public function lockExpirationAfter(?string $lockExpirationDate): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("lockExpirationAfter", "lockExpirationDate", $lockExpirationDate);
        $this->lockExpirationAfter = $lockExpirationDate;
        return $this;
    }

    public function topicName(?string $topicName): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("topicName", "topicName", $topicName);
        $this->topicName = $topicName;
        return $this;
    }

    public function locked(): ExternalTaskQueryInterface
    {
        $this->locked = true;
        return $this;
    }

    public function notLocked(): ExternalTaskQueryInterface
    {
        $this->notLocked = true;
        return $this;
    }

    public function executionId(?string $executionId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceIdIn(array $processInstanceIdIn): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceIdIn", "processInstanceIdIn", $processInstanceIdIn);
        $this->processInstanceIdIn = $processInstanceIdIn;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function activityId(?string $activityId): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("activityId", "activityId", $activityId);
        $this->activityId = $activityId;
        return $this;
    }

    public function activityIdIn(array $activityIdIn): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("activityIdIn", "activityIdIn", $activityIdIn);
        $this->activityIdIn = $activityIdIn;
        return $this;
    }

    public function priorityHigherThanOrEquals(int $priority): ExternalTaskQueryInterface
    {
        $this->priorityHigherThanOrEquals = $priority;
        return $this;
    }

    public function priorityLowerThanOrEquals(int $priority): ExternalTaskQueryInterface
    {
        $this->priorityLowerThanOrEquals = $priority;
        return $this;
    }

    public function suspended(): ExternalTaskQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function active(): ExternalTaskQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function withRetriesLeft(): ExternalTaskQueryInterface
    {
        $this->retriesLeft = true;
        return $this;
    }

    public function noRetriesLeft(): ExternalTaskQueryInterface
    {
        $this->retriesLeft = false;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
            || CompareUtil::areNotInAscendingOrder($this->priorityHigherThanOrEquals, $this->priorityLowerThanOrEquals);
    }

    public function tenantIdIn(array $tenantIds): ExternalTaskQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        return $this;
    }

    public function orderById(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::id());
    }

    public function orderByLockExpirationTime(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::lockExpirationTime());
    }

    public function orderByProcessInstanceId(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::processInstanceId());
    }

    public function orderByProcessDefinitionId(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::processDefinitionId());
    }

    public function orderByProcessDefinitionKey(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::processDefinitionKey());
    }

    public function orderByTenantId(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::tenantId());
    }

    public function orderByPriority(): ExternalTaskQueryInterface
    {
        return $this->orderBy(ExternalTaskQueryProperty::priority());
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getExternalTaskManager()
            ->findExternalTaskCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getExternalTaskManager()
            ->findExternalTasksByQueryCriteria($this);
    }

    public function executeIdsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getExternalTaskManager()
            ->findExternalTaskIdsByQueryCriteria($this);
    }

    public function executeDeploymentIdMappingsList(CommandContext $commandContext): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getExternalTaskManager()
            ->findDeploymentIdMappingsByQueryCriteria($this);
    }

    public function getExternalTaskId(): ?string
    {
        return $this->externalTaskId;
    }

    public function getWorkerId(): ?string
    {
        return $this->workerId;
    }

    public function getLockExpirationBefore(): ?string
    {
        return $this->lockExpirationBefore;
    }

    public function getLockExpirationAfter(): ?string
    {
        return $this->lockExpirationAfter;
    }

    public function getTopicName(): ?string
    {
        return $this->topicName;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function getNotLocked(): bool
    {
        return $this->notLocked;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getSuspensionState(): ?SuspensionState
    {
        return $this->suspensionState;
    }

    public function getRetriesLeft(): bool
    {
        return $this->retriesLeft;
    }

    public function getNow(): ?string
    {
        return ClockUtil::getCurrentTime()->format('c');
    }
}
