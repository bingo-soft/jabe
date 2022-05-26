<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\History\{
    ExternalTaskStateImpl,
    ExternalTaskStateInterface,
    HistoricExternalTaskLogQueryInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

class HistoricExternalTaskLogQueryImpl extends AbstractQuery implements HistoricExternalTaskLogQueryInterface
{
    protected $id;
    protected $externalTaskId;
    protected $topicName;
    protected $workerId;
    protected $errorMessage;
    protected $activityIds = [];
    protected $activityInstanceIds = [];
    protected $executionIds = [];
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $priorityHigherThanOrEqual;
    protected $priorityLowerThanOrEqual;
    protected $tenantIds = [];
    protected $isTenantIdSet;
    protected $state;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    // query parameter ////////////////////////////////////////////

    public function logId(string $historicExternalTaskLogId): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "historicExternalTaskLogId", $historicExternalTaskLogId);
        $this->id = $historicExternalTaskLogId;
        return $this;
    }

    public function externalTaskId(string $externalTaskId): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "externalTaskId", $externalTaskId);
        $this->externalTaskId = $externalTaskId;
        return $this;
    }

    public function topicName(string $topicName): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "topicName", $topicName);
        $this->topicName = $topicName;
        return $this;
    }

    public function workerId(string $workerId): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "workerId", $workerId);
        $this->workerId = $workerId;
        return $this;
    }

    public function errorMessage(string $errorMessage): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "errorMessage", $errorMessage);
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function activityIdIn(array $activityIds): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityIds", $activityIds);
        $activityIdList = CollectionUtil::asArrayList($activityIds);
        EnsureUtil::ensureNotContainsNull("activityIds", "activityIds", $activityIdList);
        EnsureUtil::ensureNotContainsEmptyString("activityIds", "activityIds", $activityIdList);
        $this->activityIds = $activityIds;
        return $this;
    }

    public function activityInstanceIdIn(array $activityInstanceIds): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityIds", $activityInstanceIds);
        $activityInstanceIdList = CollectionUtil::asArrayList($activityInstanceIds);
        EnsureUtil::ensureNotContainsNull("activityInstanceIds", $activityInstanceIdList);
        EnsureUtil::ensureNotContainsEmptyString("activityInstanceIds", $activityInstanceIdList);
        $this->activityInstanceIds = $activityInstanceIds;
        return $this;
    }

    public function executionIdIn(array $executionIds): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "activityIds", $executionIds);
        $executionIdList = CollectionUtil::asArrayList($executionIds);
        EnsureUtil::ensureNotContainsNull("executionIds", "executionIds", $executionIdList);
        EnsureUtil::ensureNotContainsEmptyString("executionIds", "executionIds", $executionIdList);
        $this->executionIds = $executionIds;
        return $this;
    }

    public function processInstanceId(string $processInstanceId): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(string $processDefinitionKey): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricExternalTaskLogQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricExternalTaskLogQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function priorityHigherThanOrEquals(int $priority): HistoricExternalTaskLogQueryInterface
    {
        $this->priorityHigherThanOrEqual = $priority;
        return $this;
    }

    public function priorityLowerThanOrEquals(int $priority): HistoricExternalTaskLogQueryInterface
    {
        $this->priorityLowerThanOrEqual = $priority;
        return $this;
    }

    public function creationLog(): HistoricExternalTaskLogQueryInterface
    {
        $this->setState(ExternalTaskStateImpl::created());
        return $this;
    }

    public function failureLog(): HistoricExternalTaskLogQueryInterface
    {
        $this->setState(ExternalTaskState::failed());
        return $this;
    }

    public function successLog(): HistoricExternalTaskLogQueryInterface
    {
        $this->setState(ExternalTaskState::successful());
        return $this;
    }

    public function deletionLog(): HistoricExternalTaskLogQueryInterface
    {
        $this->setState(ExternalTaskState::deleted());
        return $this;
    }

    // order by //////////////////////////////////////////////

    public function orderByTimestamp(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::timestamp());
        return $this;
    }

    public function orderByExternalTaskId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::externalTaskId());
        return $this;
    }

    public function orderByRetries(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::retries());
        return $this;
    }

    public function orderByPriority(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::priority());
        return $this;
    }

    public function orderByTopicName(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::topicName());
        return $this;
    }

    public function orderByWorkerId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::workerId());
        return $this;
    }

    public function orderByActivityId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::activityId());
        return $this;
    }

    public function orderByActivityInstanceId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::activityInstanceId());
        return $this;
    }

    public function orderByExecutionId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::executionId());
        return $this;
    }

    public function orderByProcessInstanceId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByProcessDefinitionKey(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::processDefinitionKey());
        return $this;
    }

    public function orderByTenantId(): HistoricExternalTaskLogQueryInterface
    {
        $this->orderBy(HistoricExternalTaskLogQueryProperty::tenantId());
        return $this;
    }

    // results //////////////////////////////////////////////////////////////

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricExternalTaskLogManager()
            ->findHistoricExternalTaskLogsCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricExternalTaskLogManager()
            ->findHistoricExternalTaskLogsByQueryCriteria($this, $page);
    }

    // getters & setters ////////////////////////////////////////////////////////////

    protected function setState(ExternalTaskStateInterface $state): void
    {
        $this->state = $state;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }
}
