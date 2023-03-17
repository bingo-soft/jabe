<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\History\HistoricActivityInstanceQueryInterface;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Pvm\Runtime\ActivityInstanceState;
use Jabe\Impl\Util\{
    CompareUtil,
    EnsureUtil
};

class HistoricActivityInstanceQueryImpl extends AbstractQuery implements HistoricActivityInstanceQueryInterface
{
    protected $activityInstanceId;
    protected $processInstanceId;
    protected $executionId;
    protected $processDefinitionId;
    protected $activityId;
    protected $activityName;
    protected $activityNameLike;
    protected $activityType;
    protected $assignee;
    protected bool $finished = false;
    protected bool $unfinished = false;
    protected $startedBefore;
    protected $startedAfter;
    protected $finishedBefore;
    protected $finishedAfter;
    protected $activityInstanceState;
    protected $tenantIds = [];
    protected bool $isTenantIdSet = false;

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
        ->getHistoricActivityInstanceManager()
        ->findHistoricActivityInstanceCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
        ->getHistoricActivityInstanceManager()
        ->findHistoricActivityInstancesByQueryCriteria($this, $page);
    }

    public function processInstanceId(?string $processInstanceId): HistoricActivityInstanceQueryImpl
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function executionId(?string $executionId): HistoricActivityInstanceQueryImpl
    {
        $this->executionId = $executionId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): HistoricActivityInstanceQueryImpl
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function activityId(?string $activityId): HistoricActivityInstanceQueryImpl
    {
        $this->activityId = $activityId;
        return $this;
    }

    public function activityName(?string $activityName): HistoricActivityInstanceQueryImpl
    {
        $this->activityName = $activityName;
        return $this;
    }

    public function activityNameLike(?string $activityNameLike): HistoricActivityInstanceQueryImpl
    {
        $this->activityNameLike = $activityNameLike;
        return $this;
    }

    public function activityType(?string $activityType): HistoricActivityInstanceQueryImpl
    {
        $this->activityType = $activityType;
        return $this;
    }

    public function taskAssignee(?string $assignee): HistoricActivityInstanceQueryImpl
    {
        $this->assignee = $assignee;
        return $this;
    }

    public function finished(): HistoricActivityInstanceQueryImpl
    {
        $this->finished = true;
        return $this;
    }

    public function unfinished(): HistoricActivityInstanceQueryImpl
    {
        $this->unfinished = true;
        return $this;
    }

    public function completeScope(): HistoricActivityInstanceQueryImpl
    {
        if ($this->activityInstanceState !== null) {
            throw new ProcessEngineException("Already querying for activity instance state <" . $this->activityInstanceState . ">");
        }

        $this->activityInstanceState = ActivityInstanceState::scopeComplete();
        return $this;
    }

    public function canceled(): HistoricActivityInstanceQueryImpl
    {
        if ($this->activityInstanceState !== null) {
            throw new ProcessEngineException("Already querying for activity instance state <" . $this->activityInstanceState . ">");
        }
        $this->activityInstanceState = ActivityInstanceState::canceled();
        return $this;
    }

    public function startedAfter(?string $date): HistoricActivityInstanceQueryImpl
    {
        $this->startedAfter = $date;
        return $this;
    }

    public function startedBefore(?string $date): HistoricActivityInstanceQueryImpl
    {
        $this->startedBefore = $date;
        return $this;
    }

    public function finishedAfter(?string $date): HistoricActivityInstanceQueryImpl
    {
        $this->finishedAfter = $date;
        return $this;
    }

    public function finishedBefore(?string $date): HistoricActivityInstanceQueryImpl
    {
        $this->finishedBefore = $date;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricActivityInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricActivityInstanceQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
        || CompareUtil::areNotInAscendingOrder($this->startedAfter, $this->startedBefore)
        || CompareUtil::areNotInAscendingOrder($this->finishedAfter, $this->finishedBefore);
    }

    // ordering /////////////////////////////////////////////////////////////////

    public function orderByHistoricActivityInstanceDuration(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::duration());
        return $this;
    }

    public function orderByHistoricActivityInstanceEndTime(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::end());
        return $this;
    }

    public function orderByExecutionId(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::executionId());
        return $this;
    }

    public function orderByHistoricActivityInstanceId(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::historicActivityInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByProcessInstanceId(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByHistoricActivityInstanceStartTime(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::start());
        return $this;
    }

    public function orderByActivityId(): HistoricActivityInstanceQueryInterface
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::activityId());
        return $this;
    }

    public function orderByActivityName(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::activityName());
        return $this;
    }

    public function orderByActivityType(): HistoricActivityInstanceQueryImpl
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::activityType());
        return $this;
    }

    public function orderPartiallyByOccurrence(): HistoricActivityInstanceQueryInterface
    {
        $this->orderBy(HistoricActivityInstanceQueryProperty::sequenceCounter());
        return $this;
    }

    public function orderByTenantId(): HistoricActivityInstanceQueryInterface
    {
        return orderBy(HistoricActivityInstanceQueryProperty::tenantId());
    }

    public function activityInstanceId(?string $activityInstanceId): HistoricActivityInstanceQueryImpl
    {
        $this->activityInstanceId = $activityInstanceId;
        return $this;
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function getActivityType(): ?string
    {
        return $this->activityType;
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function isUnfinished(): bool
    {
        return $this->unfinished;
    }

    public function getActivityInstanceId(): ?string
    {
        return $this->activityInstanceId;
    }

    public function getStartedAfter(): ?string
    {
        return $this->startedAfter;
    }

    public function getStartedBefore(): ?string
    {
        return $this->startedBefore;
    }

    public function getFinishedAfter(): ?string
    {
        return $this->finishedAfter;
    }

    public function getFinishedBefore(): ?string
    {
        return $this->finishedBefore;
    }

    public function getActivityInstanceState(): ?ActivityInstanceState
    {
        return $this->activityInstanceState;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }
}
