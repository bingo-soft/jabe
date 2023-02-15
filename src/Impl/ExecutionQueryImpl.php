<?php

namespace Jabe\Impl;

use Jabe\Impl\Event\EventType;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\SuspensionState;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\ExecutionQueryInterface;

class ExecutionQueryImpl extends AbstractVariableQueryImpl implements ExecutionQueryInterface
{
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $businessKey;
    protected $activityId;
    protected $executionId;
    protected $processInstanceId;
    protected $eventSubscriptions;
    protected $suspensionState;
    protected $incidentType;
    protected $incidentId;
    protected $incidentMessage;
    protected $incidentMessageLike;

    protected bool $isTenantIdSet = false;
    protected $tenantIds = [];

    public function __construct(?CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function processDefinitionId(?string $processDefinitionId): ExecutionQueryImpl
    {
        EnsureUtil::ensureNotNull("Process definition id", "processDefinitionId", $processDefinitionId);
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): ExecutionQueryImpl
    {
        EnsureUtil::ensureNotNull("Process definition key", "processDefinitionKey", $processDefinitionKey);
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processInstanceId(?string $processInstanceId): ExecutionQueryImpl
    {
        EnsureUtil::ensureNotNull("Process instance id", "processInstanceId", $processInstanceId);
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceBusinessKey(?string $businessKey): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("Business key", "businessKey", $businessKey);
        $this->businessKey = $businessKey;
        return $this;
    }

    public function executionId(?string $executionId): ExecutionQueryImpl
    {
        EnsureUtil::ensureNotNull("Execution id", "executionId", $executionId);
        $this->executionId = $executionId;
        return $this;
    }

    public function activityId(?string $activityId): ExecutionQueryImpl
    {
        $this->activityId = $activityId;
        return $this;
    }

    public function signalEventSubscription(?string $signalName): ExecutionQueryInterface
    {
        return $this->eventSubscription(EventType::signal(), $signalName);
    }

    public function signalEventSubscriptionName(?string $signalName): ExecutionQueryInterface
    {
        return $this->eventSubscription(EventType::signal(), $signalName);
    }

    public function messageEventSubscriptionName(?string $messageName): ExecutionQueryInterface
    {
        return $this->eventSubscription(EventType::message(), $messageName);
    }

    public function messageEventSubscription(): ExecutionQueryInterface
    {
        return $this->eventSubscription(EventType::message(), null);
    }

    public function eventSubscription(EventType $eventType, ?string $eventName): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("event type", "eventType", $eventType);
        if (EventType::message() != $eventType) {
            // event name is optional for message events
            EnsureUtil::ensureNotNull("event name", "eventName", $eventName);
        }
        if (empty($this->eventSubscriptions)) {
            $this->eventSubscriptions = [];
        }
        $this->eventSubscriptions[] = new EventSubscriptionQueryValue($eventName, $eventType->name());
        return $this;
    }

    public function suspended(): ExecutionQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function active(): ExecutionQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function processVariableValueEquals(?string $variableName, $variableValue): ExecutionQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, false);
        return $this;
    }

    public function processVariableValueNotEquals(?string $variableName, $variableValue): ExecutionQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_EQUALS, false);
        return $this;
    }

    public function incidentType(?string $incidentType): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident type", "incidentType", $incidentType);
        $this->incidentType = $incidentType;
        return $this;
    }

    public function incidentId(?string $incidentId): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident id", "incidentId", $incidentId);
        $this->incidentId = $incidentId;
        return $this;
    }

    public function incidentMessage(?string $incidentMessage): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident message", "incidentMessage", $incidentMessage);
        $this->incidentMessage = $incidentMessage;
        return $this;
    }

    public function incidentMessageLike(?string $incidentMessageLike): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("incident messageLike", "incidentMessageLike", $incidentMessageLike);
        $this->incidentMessageLike = $incidentMessageLike;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): ExecutionQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): ExecutionQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    //ordering ////////////////////////////////////////////////////

    public function orderByProcessInstanceId(): ExecutionQueryImpl
    {
        $this->orderBy(ExecutionQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): ExecutionQueryImpl
    {
        $this->orderBy(new QueryOrderingProperty(QueryOrderingProperty::RELATION_PROCESS_DEFINITION, ExecutionQueryProperty::processDefinitionId()));
        return $this;
    }

    public function orderByProcessDefinitionKey(): ExecutionQueryImpl
    {
        $this->orderBy(new QueryOrderingProperty(QueryOrderingProperty::RELATION_PROCESS_DEFINITION, ExecutionQueryProperty::processDefinitionKey()));
        return $this;
    }

    public function orderByTenantId(): ExecutionQueryInterface
    {
        $this->orderBy(ExecutionQueryProperty::tenantId());
        return $this;
    }

    //results ////////////////////////////////////////////////////
    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        $this->ensureVariablesInitialized();
        return $commandContext
        ->getExecutionManager()
        ->findExecutionCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        $this->ensureVariablesInitialized();
        return $commandContext
        ->getExecutionManager()
        ->findExecutionsByQueryCriteria($this, $page);
    }

    //getters ////////////////////////////////////////////////////

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function getActivityId(): ?string
    {
        return $this->activityId;
    }

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceIds(): array
    {
        return [];
    }

    public function getBusinessKey(): ?string
    {
        return $this->businessKey;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getSuspensionState(): ?SuspensionState
    {
        return $this->suspensionState;
    }

    public function setSuspensionState(SuspensionState $suspensionState): void
    {
        $this->suspensionState = $suspensionState;
    }

    public function getEventSubscriptions(): ?array
    {
        return $this->eventSubscriptions;
    }

    public function setEventSubscriptions(array $eventSubscriptions): void
    {
        $this->eventSubscriptions = $eventSubscriptions;
    }

    public function getIncidentId(): ?string
    {
        return $this->incidentId;
    }

    public function getIncidentType(): ?string
    {
        return $this->incidentType;
    }

    public function getIncidentMessage(): ?string
    {
        return $this->incidentMessage;
    }

    public function getIncidentMessageLike(): ?string
    {
        return $this->incidentMessageLike;
    }
}
