<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\History\{
    HistoricIncidentQueryInterface,
    IncidentStateImpl,
    IncidentStateInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\EnsureUtil;

class HistoricIncidentQueryImpl extends AbstractVariableQueryImpl implements HistoricIncidentQueryInterface
{
    protected $id;
    protected $incidentType;
    protected $incidentMessage;
    protected $incidentMessageLike;
    protected $executionId;
    protected $activityId;
    protected $createTimeBefore;
    protected $createTimeAfter;
    protected $endTimeBefore;
    protected $endTimeAfter;
    protected $failedActivityId;
    protected $processInstanceId;
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $causeIncidentId;
    protected $rootCauseIncidentId;
    protected $configuration;
    protected $historyConfiguration;
    protected $incidentState;
    protected $tenantIds = [];
    protected bool $isTenantIdSet = false;
    protected $jobDefinitionIds = [];

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function incidentId(?string $incidentId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentId", "incidentId", $incidentId);
        $this->id = $incidentId;
        return $this;
    }

    public function incidentType(?string $incidentType): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentType", "incidentType", $incidentType);
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentMessage(?string $incidentMessage): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentMessage", "incidentMessage", $incidentMessage);
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(?string $incidentMessageLike): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("incidentMessageLike", "incidentMessageLike", $incidentMessageLike);
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    public function executionId(?string $executionId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function createTimeBefore(?string $createTimeBefore): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("createTimeBefore", "createTimeBefore", $createTimeBefore);
        $this->createTimeBefore = $createTimeBefore;
        return $this;
    }

    public function createTimeAfter(?string $createTimeAfter): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("createTimeAfter", "createTimeAfter", $createTimeAfter);
        $this->createTimeAfter = $createTimeAfter;
        return $this;
    }

    public function endTimeBefore(?string $endTimeBefore): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("endTimeBefore", "endTimeBefore", $endTimeBefore);
        $this->endTimeBefore = $endTimeBefore;
        return $this;
    }

    public function endTimeAfter(?string $endTimeAfter): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("endTimeAfter", "endTimeAfter", $endTimeAfter);
        $this->endTimeAfter = $endTimeAfter;
        return $this;
    }

    public function activityId(?string $activityId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("activityId", "activityId", $activityId);
        $this->activityId = $activityId;
        return $this;
    }

    public function failedActivityId(?string $activityId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("failedActivityId", "activityId", $activityId);
        $this->failedActivityId = $activityId;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("processDefinitionKeys", "processDefinitionKeys", $processDefinitionKeys);
        $this->processDefinitionKeys = $processDefinitionKeys;
        return $this;
    }

    public function causeIncidentId(?string $causeIncidentId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("causeIncidentId", "causeIncidentId", $causeIncidentId);
        $this->causeIncidentId = $causeIncidentId;
        return $this;
    }

    public function rootCauseIncidentId(?string $rootCauseIncidentId): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("rootCauseIncidentId", "rootCauseIncidentId", $rootCauseIncidentId);
        $this->rootCauseIncidentId = $rootCauseIncidentId;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricIncidentQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function configuration(?string $configuration): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("configuration", "configuration", $configuration);
        $this->configuration = $configuration;
        return $this;
    }

    public function historyConfiguration(?string $historyConfiguration): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("historyConfiguration", "historyConfiguration", $historyConfiguration);
        $this->historyConfiguration = $historyConfiguration;
        return $this;
    }

    public function jobDefinitionIdIn(array $jobDefinitionIds): HistoricIncidentQueryInterface
    {
        EnsureUtil::ensureNotNull("jobDefinitionIds", "jobDefinitionIds", $jobDefinitionIds);
        $this->jobDefinitionIds = $jobDefinitionIds;
        return $this;
    }

    public function open(): HistoricIncidentQueryInterface
    {
        if ($this->incidentState !== null) {
            throw new ProcessEngineException("Already querying for incident state <" . $this->incidentState . ">");
        }
        $this->incidentState = IncidentStateImpl::default();
        return $this;
    }

    public function resolved(): HistoricIncidentQueryInterface
    {
        if ($this->incidentState !== null) {
            throw new ProcessEngineException("Already querying for incident state <" . $this->incidentState . ">");
        }
        $this->incidentState = IncidentStateImpl::resolved();
        return $this;
    }

    public function deleted(): HistoricIncidentQueryInterface
    {
        if ($this->incidentState !== null) {
            throw new ProcessEngineException("Already querying for incident state <" . $this->incidentState . ">");
        }
        $this->incidentState = IncidentStateImpl::deleted();
        return $this;
    }

    // ordering ////////////////////////////////////////////////////

    public function orderByIncidentId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentId());
        return $this;
    }

    public function orderByIncidentMessage(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentMessage());
        return $this;
    }

    public function orderByCreateTime(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentCreateTime());
        return $this;
    }

    public function orderByEndTime(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentEndTime());
        return $this;
    }

    public function orderByIncidentType(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentType());
        return $this;
    }

    public function orderByExecutionId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::executionId());
        return $this;
    }

    public function orderByActivityId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::activityId());
        return $this;
    }

    public function orderByProcessInstanceId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionKey(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::processDefinitionKey());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByCauseIncidentId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::causeIncidentId());
        return $this;
    }

    public function orderByRootCauseIncidentId(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::rootCauseIncidentId());
        return $this;
    }

    public function orderByConfiguration(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::configuration());
        return $this;
    }

    public function orderByHistoryConfiguration(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::historyConfiguration());
        return $this;
    }

    public function orderByIncidentState(): HistoricIncidentQueryInterface
    {
        $this->orderBy(HistoricIncidentQueryProperty::incidentState());
        return $this;
    }

    public function orderByTenantId(): HistoricIncidentQueryInterface
    {
        return $this->orderBy(HistoricIncidentQueryProperty::tenantId());
    }

    // results ////////////////////////////////////////////////////

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricIncidentManager()
            ->findHistoricIncidentCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricIncidentManager()
            ->findHistoricIncidentByQueryCriteria($this, $page);
    }

    // getters /////////////////////////////////////////////////////

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getIncidentType(): ?string
    {
        return $this->incidentType;
    }

    public function getIncidentMessage(): ?string
    {
        return $this->incidentMessage;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getFailedActivityId(): ?string
    {
        return $this->failedActivityId;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }

    public function getCauseIncidentId(): ?string
    {
        return $this->causeIncidentId;
    }

    public function getRootCauseIncidentId(): ?string
    {
        return $this->rootCauseIncidentId;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function getHistoryConfiguration(): ?string
    {
        return $this->historyConfiguration;
    }

    public function getIncidentState(): IncidentStateInterface
    {
        return $this->incidentState;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }
}
