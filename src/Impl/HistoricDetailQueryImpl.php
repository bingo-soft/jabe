<?php

namespace Jabe\Impl;

use Jabe\History\HistoricDetailQueryInterface;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\HistoricDetailVariableInstanceUpdateEntity;
use Jabe\Impl\Variable\Serializer\AbstractTypedValueSerializer;

class HistoricDetailQueryImpl extends AbstractQuery implements HistoricDetailQueryInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $detailId;
    protected $taskId;
    protected $processInstanceId;
    //protected String caseInstanceId;
    protected $executionId;
    //protected String caseExecutionId;
    protected $activityId;
    protected $activityInstanceId;
    protected $type;
    protected $variableInstanceId;
    protected $variableTypes = [];
    protected $tenantIds = [];
    protected bool $isTenantIdSet = false;
    protected $processInstanceIds = [];
    protected $userOperationId;
    protected $sequenceCounter;
    protected $occurredBefore;
    protected $occurredAfter;
    protected bool $initial = false;
    protected bool $excludeTaskRelated = false;
    protected bool $isByteArrayFetchingEnabled = true;
    protected bool $isCustomObjectDeserializationEnabled = true;

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function detailId(?string $id): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("detailId", "id", $id);
        $this->detailId = $id;
        return $this;
    }

    public function variableInstanceId(?string $variableInstanceId): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("variableInstanceId", "variableInstanceId", $variableInstanceId);
        $this->variableInstanceId = $variableInstanceId;
        return $this;
    }

    public function variableTypeIn(array $variableTypes): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("Variable types", "variableTypes", $variableTypes);
        $this->variableTypes = $this->lowerCase($variableTypes);
        return $this;
    }

    private function lowerCase(array $variableTypes): array
    {
        for ($i = 0; $i < count($variableTypes); $i += 1) {
            $variableTypes[$i] = strtolower($variableTypes[$i]);
        }
        return $variableTypes;
    }

    public function processInstanceId(?string $processInstanceId): HistoricDetailQueryInterface
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    /*public HistoricDetailQueryInterface caseInstanceId(?string $caseInstanceId) {
        EnsureUtil::ensureNotNull("Case instance id", caseInstanceId);
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }*/

    public function executionId(?string $executionId): HistoricDetailQueryInterface
    {
        $this->executionId = $executionId;
        return $this;
    }

    /*public HistoricDetailQueryInterface caseExecutionId(?string $caseExecutionId) {
        EnsureUtil::ensureNotNull("Case execution id", caseExecutionId);
        $this->caseExecutionId = caseExecutionId;
        return $this;
    }*/

    public function activityId(?string $activityId): HistoricDetailQueryInterface
    {
        $this->activityId = $activityId;
        return $this;
    }

    public function activityInstanceId(?string $activityInstanceId): HistoricDetailQueryInterface
    {
        $this->activityInstanceId = $activityInstanceId;
        return $this;
    }

    public function taskId(?string $taskId): HistoricDetailQueryInterface
    {
        $this->taskId = $taskId;
        return $this;
    }

    public function formProperties(): HistoricDetailQueryInterface
    {
        $this->type = "FormProperty";
        return $this;
    }

    public function formFields(): HistoricDetailQueryInterface
    {
        $this->type = "FormProperty";
        return $this;
    }

    public function variableUpdates(): HistoricDetailQueryInterface
    {
        $this->type = "VariableUpdate";
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricDetailQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function processInstanceIdIn(array $processInstanceIds): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("Process Instance Ids", "processInstanceIds", $processInstanceIds);
        $this->processInstanceIds = $processInstanceIds;
        return $this;
    }

    public function userOperationId(?string $userOperationId): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("userOperationId", "userOperationId", $userOperationId);
        $this->userOperationId = $userOperationId;
        return $this;
    }

    public function sequenceCounter(int $sequenceCounter): HistoricDetailQueryImpl
    {
        $this->sequenceCounter = $sequenceCounter;
        return $this;
    }

    public function excludeTaskDetails(): HistoricDetailQueryInterface
    {
        $this->excludeTaskRelated = true;
        return $this;
    }

    public function occurredBefore(?string $date): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("occurred before", "date", $date);
        $this->occurredBefore = $date;
        return $this;
    }

    public function occurredAfter(?string $date): HistoricDetailQueryInterface
    {
        EnsureUtil::ensureNotNull("occurred after", "date", $date);
        $this->occurredAfter = $date;
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricDetailManager()
            ->findHistoricDetailCountByQueryCriteria($this);
    }

    public function disableBinaryFetching(): HistoricDetailQueryInterface
    {
        $this->isByteArrayFetchingEnabled = false;
        return $this;
    }

    public function disableCustomObjectDeserialization(): HistoricDetailQueryInterface
    {
        $this->isCustomObjectDeserializationEnabled = false;
        return $this;
    }

    public function initial(): HistoricDetailQueryInterface
    {
        $this->initial = true;
        return $this;
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        $historicDetails = $commandContext
            ->getHistoricDetailManager()
            ->findHistoricDetailsByQueryCriteria($this, $page);
        if (!empty($historicDetails)) {
            foreach ($historicDetails as $historicDetail) {
                if ($historicDetail instanceof HistoricDetailVariableInstanceUpdateEntity) {
                    $entity = $historicDetail;
                    if ($this->shouldFetchValue($entity)) {
                        try {
                            $entity->getTypedValue($this->isCustomObjectDeserializationEnabled);
                        } catch (\Throwable $t) {
                            // do not fail if one of the variables fails to load
                            //LOG.exceptionWhileGettingValueForVariable(t);
                        }
                    }
                }
            }
        }
        return $historicDetails;
    }

    protected function shouldFetchValue(HistoricDetailVariableInstanceUpdateEntity $entity): bool
    {
        // do not fetch values for byte arrays eagerly (unless requested by the user)
        return $this->isByteArrayFetchingEnabled
            || !in_array($entity->getSerializer()->getType()->getName(), AbstractTypedValueSerializer::BINARY_VALUE_TYPES);
    }

    // order by /////////////////////////////////////////////////////////////////

    public function orderByProcessInstanceId(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByTime(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::time());
        return $this;
    }

    public function orderByVariableName(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::variableName());
        return $this;
    }

    public function orderByFormPropertyId(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::variableName());
        return $this;
    }

    public function orderByVariableRevision(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::variableRevision());
        return $this;
    }

    public function orderByVariableType(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::variableType());
        return $this;
    }

    public function orderPartiallyByOccurrence(): HistoricDetailQueryInterface
    {
        $this->orderBy(HistoricDetailQueryProperty::sequenceCounter());
        return $this;
    }

    public function orderByTenantId(): HistoricDetailQueryInterface
    {
        return orderBy(HistoricDetailQueryProperty::tenantId());
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    /*public String getCaseInstanceId() {
        return caseInstanceId;
    }*/

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    /*public function getCaseExecutionId(): ?string
    {
      return caseExecutionId;
    }*/

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getExcludeTaskRelated(): bool
    {
        return $this->excludeTaskRelated;
    }

    public function getDetailId(): ?string
    {
        return $this->detailId;
    }

    public function getProcessInstanceIds(): array
    {
        return $this->processInstanceIds;
    }

    public function getOccurredBefore(): ?string
    {
        return $this->occurredBefore;
    }

    public function getOccurredAfter(): ?string
    {
        return $this->occurredAfter;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }
}
