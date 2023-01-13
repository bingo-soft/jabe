<?php

namespace Jabe\Impl;

use Jabe\History\HistoricIdentityLinkLogQueryInterface;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;

class HistoricIdentityLinkLogQueryImpl extends AbstractVariableQueryImpl implements HistoricIdentityLinkLogQueryInterface
{
    protected $dateBefore;
    protected $dateAfter;
    protected $type;
    protected $userId;
    protected $groupId;
    protected $taskId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $operationType;
    protected $assignerId;
    protected $tenantIds = [];
    protected bool $isTenantIdSet = false;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getOperationType(): ?string
    {
        return $this->operationType;
    }

    public function getAssignerId(): ?string
    {
        return $this->assignerId;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function tenantIdIn(array $tenantIds): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function getDateBefore(): ?string
    {
        return $this->dateBefore;
    }

    public function getDateAfter(): ?string
    {
        return $this->dateAfter;
    }

    public function type(?string $type): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("type", "type", $type);
        $this->type = $type;
        return $this;
    }

    public function dateBefore(?string $dateBefore): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("dateBefore", "dateBefore", $dateBefore);
        $this->dateBefore = $dateBefore;
        return $this;
    }

    public function dateAfter(?string $dateAfter): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("dateAfter", "dateAfter", $dateAfter);
        $this->dateAfter = $dateAfter;
        return $this;
    }

    public function userId(?string $userId): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("userId", "userId", $userId);
        $this->userId = $userId;
        return $this;
    }

    public function groupId(?string $groupId): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("groupId", "groupId", $groupId);
        $this->groupId = $groupId;
        return $this;
    }

    public function taskId(?string $taskId): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("taskId", "taskId", $taskId);
        $this->taskId = $taskId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function operationType(?string $operationType): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("operationType", "operationType", $operationType);
        $this->operationType = $operationType;
        return $this;
    }

    public function assignerId(?string $assignerId): HistoricIdentityLinkLogQueryInterface
    {
        EnsureUtil::ensureNotNull("assignerId", "assignerId", $assignerId);
        $this->assignerId = $assignerId;
        return $this;
    }

    public function orderByTime(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::time());
        return $this;
    }

    public function orderByType(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::type());
        return $this;
    }

    public function orderByUserId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::userId());
        return $this;
    }

    public function orderByGroupId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::groupId());
        return $this;
    }

    public function orderByTaskId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::taskId());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByProcessDefinitionKey(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::processDefinitionKey());
        return $this;
    }

    public function orderByOperationType(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::operationType());
        return $this;
    }

    public function orderByAssignerId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::assignerId());
        return $this;
    }

    public function orderByTenantId(): HistoricIdentityLinkLogQueryInterface
    {
        $this->orderBy(HistoricIdentityLinkLogQueryProperty::tenantId());
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
        ->getHistoricIdentityLinkManager()
        ->findHistoricIdentityLinkLogCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
        ->getHistoricIdentityLinkManager()
        ->findHistoricIdentityLinkLogByQueryCriteria($this, $page);
    }
}
