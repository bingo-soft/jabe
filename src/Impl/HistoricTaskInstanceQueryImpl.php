<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Exception\NotValidException;
use Jabe\History\{
    HistoricTaskInstanceInterface,
    HistoricTaskInstanceQueryInterface
};
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Util\{
    CompareUtil,
    EnsureUtil
};

class HistoricTaskInstanceQueryImpl extends AbstractQuery implements HistoricTaskInstanceQueryInterface
{
    protected $processDefinitionId;
    protected $processDefinitionKey;
    protected $processDefinitionName;
    protected $processInstanceId;
    protected $processInstanceBusinessKey;
    protected $processInstanceBusinessKeys = [];
    protected $processInstanceBusinessKeyLike;
    protected $executionId;
    protected $activityInstanceIds = [];
    protected $taskId;
    protected $taskName;
    protected $taskNameLike;
    protected $taskParentTaskId;
    protected $taskDescription;
    protected $taskDescriptionLike;
    protected $taskDeleteReason;
    protected $taskDeleteReasonLike;
    protected $taskOwner;
    protected $taskOwnerLike;
    protected $assigned;
    protected $unassigned;
    protected $taskAssignee;
    protected $taskAssigneeLike;
    protected $taskDefinitionKeys = [];
    protected $taskInvolvedUser;
    protected $taskInvolvedGroup;
    protected $taskHadCandidateUser;
    protected $taskHadCandidateGroup;
    protected $withCandidateGroups;
    protected $withoutCandidateGroups;
    protected $taskPriority;
    protected $finished;
    protected $unfinished;
    protected $processFinished;
    protected $processUnfinished;
    protected $variables = [];
    protected $variableNamesIgnoreCase;
    protected $variableValuesIgnoreCase;

    protected $dueDate;
    protected $dueAfter;
    protected $dueBefore;
    protected bool $isWithoutTaskDueDate = false;

    protected $followUpDate;
    protected $followUpBefore;
    protected $followUpAfter;

    protected $tenantIds = [];
    protected bool $isTenantIdSet = false;

    /*protected $caseDefinitionId;
    protected $caseDefinitionKey;
    protected $caseDefinitionName;
    protected $caseInstanceId;
    protected $caseExecutionId;*/

    protected $finishedAfter;
    protected $finishedBefore;
    protected $startedAfter;
    protected $startedBefore;

    protected $queries = [];
    protected bool $isOrQueryActive = false;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
        $this->queries[] = $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->ensureVariablesInitialized();
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricTaskInstanceManager()
            ->findHistoricTaskInstanceCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->ensureVariablesInitialized();
        $this->checkQueryOk();
        return $commandContext
            ->getHistoricTaskInstanceManager()
            ->findHistoricTaskInstancesByQueryCriteria($this, $page);
    }

    public function processInstanceId(?string $processInstanceId): HistoricTaskInstanceQueryImpl
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceBusinessKey(?string $processInstanceBusinessKey): HistoricTaskInstanceQueryInterface
    {
        $this->processInstanceBusinessKey = $processInstanceBusinessKey;
        return $this;
    }

    public function processInstanceBusinessKeyIn(array $processInstanceBusinessKeys): HistoricTaskInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceBusinessKeys", "processInstanceBusinessKeys", $processInstanceBusinessKeys);
        $this->processInstanceBusinessKeys = $processInstanceBusinessKeys;
        return $this;
    }

    public function processInstanceBusinessKeyLike(?string $processInstanceBusinessKey): HistoricTaskInstanceQueryInterface
    {
        $this->processInstanceBusinessKeyLike = $processInstanceBusinessKey;
        return $this;
    }

    public function executionId(?string $executionId): HistoricTaskInstanceQueryImpl
    {
        $this->executionId = $executionId;
        return $this;
    }

    public function activityInstanceIdIn(array $activityInstanceIds): HistoricTaskInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("activityInstanceIds", "activityInstanceIds", $activityInstanceIds);
        $this->activityInstanceIds = $activityInstanceIds;
        return $this;
    }

    public function processDefinitionId(?string $processDefinitionId): HistoricTaskInstanceQueryImpl
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionKey(?string $processDefinitionKey): HistoricTaskInstanceQueryInterface
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionName(?string $processDefinitionName): HistoricTaskInstanceQueryInterface
    {
        $this->processDefinitionName = $processDefinitionName;
        return $this;
    }

    public function taskId(?string $taskId): HistoricTaskInstanceQueryInterface
    {
        $this->taskId = $taskId;
        return $this;
    }

    public function taskName(?string $taskName): HistoricTaskInstanceQueryImpl
    {
        $this->taskName = $taskName;
        return $this;
    }

    public function taskNameLike(?string $taskNameLike): HistoricTaskInstanceQueryImpl
    {
        $this->taskNameLike = $taskNameLike;
        return $this;
    }

    public function taskParentTaskId(?string $parentTaskId): HistoricTaskInstanceQueryInterface
    {
        $this->taskParentTaskId = $parentTaskId;
        return $this;
    }

    public function taskDescription(?string $taskDescription): HistoricTaskInstanceQueryImpl
    {
        $this->taskDescription = $taskDescription;
        return $this;
    }

    public function taskDescriptionLike(?string $taskDescriptionLike): HistoricTaskInstanceQueryImpl
    {
        $this->taskDescriptionLike = $taskDescriptionLike;
        return $this;
    }

    public function taskDeleteReason(?string $taskDeleteReason): HistoricTaskInstanceQueryImpl
    {
        $this->taskDeleteReason = $taskDeleteReason;
        return $this;
    }

    public function taskDeleteReasonLike(?string $taskDeleteReasonLike): HistoricTaskInstanceQueryImpl
    {
        $this->taskDeleteReasonLike = $taskDeleteReasonLike;
        return $this;
    }

    public function taskAssigned(): HistoricTaskInstanceQueryImpl
    {
        $this->assigned = true;
        return $this;
    }

    public function taskUnassigned(): HistoricTaskInstanceQueryImpl
    {
        $this->unassigned = true;
        return $this;
    }

    public function taskAssignee(?string $taskAssignee): HistoricTaskInstanceQueryImpl
    {
        $this->taskAssignee = $taskAssignee;
        return $this;
    }

    public function taskAssigneeLike(?string $taskAssigneeLike): HistoricTaskInstanceQueryImpl
    {
        $this->taskAssigneeLike = $taskAssigneeLike;
        return $this;
    }

    public function taskOwner(?string $taskOwner): HistoricTaskInstanceQueryImpl
    {
        $this->taskOwner = $taskOwner;
        return $this;
    }

    public function taskOwnerLike(?string $taskOwnerLike): HistoricTaskInstanceQueryImpl
    {
        $this->taskOwnerLike = $taskOwnerLike;
        return $this;
    }

    /*public HistoricTaskInstanceQueryInterface caseDefinitionId(?string $caseDefinitionId) {
        $this->caseDefinitionId = caseDefinitionId;
        return $this;
    }

    public HistoricTaskInstanceQueryInterface caseDefinitionKey(?string $caseDefinitionKey) {
        $this->caseDefinitionKey = caseDefinitionKey;
        return $this;
    }

    public HistoricTaskInstanceQueryInterface caseDefinitionName(?string $caseDefinitionName) {
        $this->caseDefinitionName = caseDefinitionName;
        return $this;
    }

    public HistoricTaskInstanceQueryInterface caseInstanceId(?string $caseInstanceId) {
        $this->caseInstanceId = caseInstanceId;
        return $this;
    }

    public HistoricTaskInstanceQueryInterface caseExecutionId(?string $caseExecutionId) {
        $this->caseExecutionId = caseExecutionId;
        return $this;
    }*/

    public function finished(): HistoricTaskInstanceQueryImpl
    {
        $this->finished = true;
        return $this;
    }

    public function unfinished(): HistoricTaskInstanceQueryImpl
    {
        $this->unfinished = true;
        return $this;
    }

    public function matchVariableNamesIgnoreCase(): HistoricTaskInstanceQueryInterface
    {
        $this->variableNamesIgnoreCase = true;
        foreach ($this->variables as $variable) {
            $variable->setVariableNameIgnoreCase(true);
        }
        return $this;
    }

    public function matchVariableValuesIgnoreCase(): HistoricTaskInstanceQueryInterface
    {
        $this->variableValuesIgnoreCase = true;
        foreach ($this->variables as $variable) {
            $variable->setVariableValueIgnoreCase(true);
        }
        return $this;
    }

    public function taskVariableValueEquals(?string $variableName, $variableValue): HistoricTaskInstanceQueryImpl
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, true, false);
        return $this;
    }

    public function processVariableValueEquals(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, false, true);
        return $this;
    }

    public function processVariableValueNotEquals(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_EQUALS, false, true);
        return $this;
    }

    public function processVariableValueLike(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LIKE, false, true);
        return $this;
    }

    public function processVariableValueNotLike(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_LIKE, false, true);
        return $this;
    }

    public function processVariableValueGreaterThan(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN, false, true);
        return $this;
    }

    public function processVariableValueGreaterThanOrEquals(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN_OR_EQUAL, false, true);
        return $this;
    }

    public function processVariableValueLessThan(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN, false, true);
        return $this;
    }

    public function processVariableValueLessThanOrEquals(?string $variableName, $variableValue): HistoricTaskInstanceQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN_OR_EQUAL, false, true);
        return $this;
    }

    public function taskDefinitionKey(?string $taskDefinitionKey): HistoricTaskInstanceQueryInterface
    {
        return $this->taskDefinitionKeyIn($taskDefinitionKey);
    }

    public function taskDefinitionKeyIn(array $taskDefinitionKeys): HistoricTaskInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "taskDefinitionKeys", $taskDefinitionKeys);
        $this->taskDefinitionKeys = $taskDefinitionKeys;
        return $this;
    }

    public function taskPriority(int $taskPriority): HistoricTaskInstanceQueryInterface
    {
        $this->taskPriority = $taskPriority;
        return $this;
    }

    public function processFinished(): HistoricTaskInstanceQueryInterface
    {
        $this->processFinished = true;
        return $this;
    }

    public function taskInvolvedUser(?string $userId): HistoricTaskInstanceQueryInterface
    {
        $this->taskInvolvedUser = $userId;
        return $this;
    }

    public function taskInvolvedGroup(?string $groupId): HistoricTaskInstanceQueryInterface
    {
        $this->taskInvolvedGroup = $groupId;
        return $this;
    }

    public function taskHadCandidateUser(?string $userId): HistoricTaskInstanceQueryInterface
    {
        $this->taskHadCandidateUser = $userId;
        return $this;
    }

    public function taskHadCandidateGroup(?string $groupId): HistoricTaskInstanceQueryInterface
    {
        $this->taskHadCandidateGroup = $groupId;
        return $this;
    }

    public function withCandidateGroups(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withCandidateGroups() within 'or' query");
        }

        $this->withCandidateGroups = true;
        return $this;
    }

    public function withoutCandidateGroups(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withoutCandidateGroups() within 'or' query");
        }

        $this->withoutCandidateGroups = true;
        return $this;
    }

    public function processUnfinished(): HistoricTaskInstanceQueryInterface
    {
        $this->processUnfinished = true;
        return $this;
    }

    protected function ensureVariablesInitialized(): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $variableSerializers = $processEngineConfiguration->getVariableSerializers();
        $dbType = $processEngineConfiguration->getDatabaseType();
        foreach ($this->variables as $var) {
            $var->initialize($variableSerializers, $dbType);
        }

        if (!empty($this->queries)) {
            foreach ($this->queries as $orQuery) {
                foreach ($orQuery->variables as $var) {
                    $var->initialize($variableSerializers, $dbType);
                }
            }
        }
    }

    public function addVariable($nameOrValue, $value = null, ?string $operator = null, bool $isTaskVariable = null, bool $isProcessInstanceVariable = null): void
    {
        if ($nameOrValue instanceof TaskQueryVariableValue) {
            $this->variables[] = $nameOrValue;
        } else {
            EnsureUtil::ensureNotNull("name", "name", $nameOrValue);
            if ($value === null || $this->isBoolean($value)) {
                // Null-values and booleans can only be used in EQUALS and NOT_EQUALS
                switch ($operator) {
                    case QueryOperator::GREATER_THAN:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'greater than' condition");
                    case QueryOperator::LESS_THAN:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'less than' condition");
                    case QueryOperator::GREATER_THAN_OR_EQUAL:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'greater than or equal' condition");
                    case QueryOperator::LESS_THAN_OR_EQUAL:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'less than or equal' condition");
                    case QueryOperator::LIKE:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'like' condition");
                    case QueryOperator::NOT_LIKE:
                        throw new ProcessEngineException("Booleans and null cannot be used in 'not like' condition");
                    default:
                        break;
                }
            }
            $shouldMatchVariableValuesIgnoreCase = $this->variableValuesIgnoreCase && $value !== null && is_string($value);
            $shouldMatchVariableNamesIgnoreCase = $this->variableNamesIgnoreCase;
            $this->addVariable(
                new TaskQueryVariableValue($name, $value, $operator, $isTaskVariable, $isProcessInstanceVariable, $shouldMatchVariableNamesIgnoreCase, $shouldMatchVariableValuesIgnoreCase)
            );
        }
    }

    private function isBoolean($value): bool
    {
        if ($value === null) {
            return false;
        }
        return is_bool($value) || strtolower($value) === "true" || strtolower($value) === "false";
    }

    public function taskDueDate(?string $dueDate): HistoricTaskInstanceQueryInterface
    {
        // The taskDueDate filter can't be used in an AND query with
        // the withoutTaskDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutTaskDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both taskDueDate and withoutTaskDueDate filters.");
            }
        }

        $this->dueDate = $dueDate;
        return $this;
    }

    public function taskDueAfter(?string $dueAfter): HistoricTaskInstanceQueryInterface
    {
        // The taskDueAfter filter can't be used in an AND query with
        // the withoutTaskDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutTaskDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both taskDueAfter and withoutTaskDueDate filters.");
            }
        }

        $this->dueAfter = $dueAfter;
        return $this;
    }

    public function taskDueBefore(?string $dueBefore): HistoricTaskInstanceQueryInterface
    {
        // The taskDueBefore filter can't be used in an AND query with
        // the withoutTaskDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutTaskDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both taskDueBefore and withoutTaskDueDate filters.");
            }
        }

        $this->dueBefore = $dueBefore;
        return $this;
    }

    public function withoutTaskDueDate(): HistoricTaskInstanceQueryInterface
    {
        // The due date filters can't be used in an AND query with
        // the withoutTaskDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->dueAfter !== null || $this->dueBefore !== null || $this->dueDate !== null) {
                throw new ProcessEngineException("Invalid query usage: cannot set both task due date (equal to, before, or after) and withoutTaskDueDate filters.");
            }
        }

        $this->isWithoutTaskDueDate = true;
        return $this;
    }

    public function taskFollowUpDate(?string $followUpDate): HistoricTaskInstanceQueryInterface
    {
        $this->followUpDate = $followUpDate;
        return $this;
    }

    public function taskFollowUpBefore(?string $followUpBefore): HistoricTaskInstanceQueryInterface
    {
        $this->followUpBefore = $followUpBefore;
        return $this;
    }

    public function taskFollowUpAfter(?string $followUpAfter): HistoricTaskInstanceQueryInterface
    {
        $this->followUpAfter = $followUpAfter;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): HistoricTaskInstanceQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);
        $this->tenantIds = $tenantIds;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withoutTenantId(): HistoricTaskInstanceQueryInterface
    {
        $this->tenantIds = null;
        $this->isTenantIdSet = true;
        return $this;
    }

    public function finishedAfter(?string $date): HistoricTaskInstanceQueryInterface
    {
        $this->finishedAfter = $date;
        return $this;
    }

    public function finishedBefore(?string $date): HistoricTaskInstanceQueryInterface
    {
        $this->finishedBefore = $date;
        return $this;
    }

    public function startedAfter(?string $date): HistoricTaskInstanceQueryInterface
    {
        $this->startedAfter = $date;
        return $this;
    }

    public function startedBefore(?string $date): HistoricTaskInstanceQueryInterface
    {
        $this->startedBefore = $date;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
            || ($this->finished && $this->unfinished)
            || ($this->processFinished && $this->processUnfinished)
            || CompareUtil::areNotInAscendingOrder($this->startedAfter, $this->startedBefore)
            || CompareUtil::areNotInAscendingOrder($this->finishedAfter, $this->finishedBefore)
            || CompareUtil::areNotInAscendingOrder($this->dueAfter, $this->dueDate, $this->dueBefore)
            || CompareUtil::areNotInAscendingOrder($this->followUpAfter, $this->followUpDate, $this->followUpBefore)
            || CompareUtil::elementIsNotContainedInArray($this->processInstanceBusinessKey, $this->processInstanceBusinessKeys);
    }

    public function orderByTaskId(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::historicTaskInstanceId());
        return $this;
    }

    public function orderByHistoricActivityInstanceId(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByHistoricActivityInstanceId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::activityInstanceId());
        return $this;
    }

    public function orderByProcessDefinitionId(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessDefinitionId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::processDefinitionId());
        return $this;
    }

    public function orderByProcessInstanceId(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::processInstanceId());
        return $this;
    }

    public function orderByExecutionId(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByExecutionId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::executionId());
        return $this;
    }

    public function orderByHistoricTaskInstanceDuration(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByHistoricTaskInstanceDuration() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::duration());
        return $this;
    }

    public function orderByHistoricTaskInstanceEndTime(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByHistoricTaskInstanceEndTime() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::end());
        return $this;
    }

    public function orderByHistoricActivityInstanceStartTime(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByHistoricActivityInstanceStartTime() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::start());
        return $this;
    }

    public function orderByTaskName(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskName() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskName());
        return $this;
    }

    public function orderByTaskDescription(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskDescription() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskDescription());
        return $this;
    }

    public function orderByTaskAssignee(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskAssignee() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskAssignee());
        return $this;
    }

    public function orderByTaskOwner(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskOwner() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskOwner());
        return $this;
    }

    public function orderByTaskDueDate(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskDueDate() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskDueDate());
        return $this;
    }

    public function orderByTaskFollowUpDate(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskFollowUpDate() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskFollowUpDate());
        return $this;
    }

    public function orderByDeleteReason(): HistoricTaskInstanceQueryImpl
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByDeleteReason() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::deleteReason());
        return $this;
    }

    public function orderByTaskDefinitionKey(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskDefinitionKey() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty::taskDefinitionKey());
        return $this;
    }

    public function orderByTaskPriority(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskPriority() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty:: taskPriority());
        return $this;
    }

    /*public HistoricTaskInstanceQueryInterface orderByCaseDefinitionId() {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseDefinitionId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty.CASE_DEFINITION_ID);
        return $this;
    }

    public HistoricTaskInstanceQueryInterface orderByCaseInstanceId() {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseInstanceId() within 'or' query");
        }

        $this->orderBy(HistoricTaskInstanceQueryProperty.CASE_INSTANCE_ID);
        return $this;
    }

    public function orderByCaseExecutionId(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseExecutionId() within 'or' query");
        }
        $this->orderBy(HistoricTaskInstanceQueryProperty.CASE_EXECUTION_ID);
        return $this;
    }*/

    public function orderByTenantId(): HistoricTaskInstanceQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTenantId() within 'or' query");
        }
        return $this->orderBy(HistoricTaskInstanceQueryProperty::tenantId());
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessInstanceId(): ?string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceBusinessKey(): ?string
    {
        return $this->processInstanceBusinessKey;
    }

    public function getProcessInstanceBusinessKeys(): array
    {
        return $this->processInstanceBusinessKeys;
    }

    public function getProcessInstanceBusinessKeyLike(): ?string
    {
        return $this->processInstanceBusinessKeyLike;
    }

    public function getProcessDefinitionKey(): ?string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionName(): ?string
    {
        return $this->processDefinitionName;
    }

    public function getExecutionId(): ?string
    {
        return $this->executionId;
    }

    public function getActivityInstanceIds(): array
    {
        return $this->activityInstanceIds;
    }

    public function getProcessDefinitionId(): ?string
    {
        return $this->processDefinitionId;
    }

    public function isAssigned(): bool
    {
        return $this->assigned;
    }

    public function isUnassigned(): bool
    {
        return $this->unassigned;
    }

    public function isWithCandidateGroups(): bool
    {
        return $this->withCandidateGroups;
    }

    public function isWithoutCandidateGroups(): bool
    {
        return $this->withoutCandidateGroups;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function isProcessFinished(): bool
    {
        return $this->processFinished;
    }

    public function isUnfinished(): bool
    {
        return $this->unfinished;
    }

    public function isProcessUnfinished(): bool
    {
        return $this->processUnfinished;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    public function getDueBefore(): ?string
    {
        return $this->dueBefore;
    }

    public function getDueAfter(): ?string
    {
        return $this->dueAfter;
    }

    public function isWithoutTaskDueDate(): bool
    {
        return $this->isWithoutTaskDueDate;
    }

    public function getFollowUpDate(): ?string
    {
        return $this->followUpDate;
    }

    public function getFollowUpBefore(): ?string
    {
        return $this->followUpBefore;
    }

    public function getFollowUpAfter(): ?string
    {
        return $this->followUpAfter;
    }

    public function getTaskName(): ?string
    {
        return $this->taskName;
    }

    public function getTaskNameLike(): ?string
    {
        return $this->taskNameLike;
    }

    public function getTaskDescription(): ?string
    {
        return $this->taskDescription;
    }

    public function getTaskDescriptionLike(): ?string
    {
        return $this->taskDescriptionLike;
    }

    public function getTaskDeleteReason(): ?string
    {
        return $this->taskDeleteReason;
    }

    public function getTaskDeleteReasonLike(): ?string
    {
        return $this->taskDeleteReasonLike;
    }

    public function getTaskAssignee(): ?string
    {
        return $this->taskAssignee;
    }

    public function getTaskAssigneeLike(): ?string
    {
        return $this->taskAssigneeLike;
    }

    public function getTaskId(): ?string
    {
        return $this->taskId;
    }

    public function getTaskInvolvedGroup(): ?string
    {
        return $this->taskInvolvedGroup;
    }

    public function getTaskInvolvedUser(): ?string
    {
        return $this->taskInvolvedUser;
    }

    public function getTaskHadCandidateGroup(): ?string
    {
        return $this->taskHadCandidateGroup;
    }

    public function getTaskHadCandidateUser(): ?string
    {
        return $this->taskHadCandidateUser;
    }

    public function getTaskDefinitionKeys(): array
    {
        return $this->taskDefinitionKeys;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getVariableNamesIgnoreCase(): bool
    {
        return $this->variableNamesIgnoreCase;
    }

    public function getVariableValuesIgnoreCase(): bool
    {
        return $this->variableValuesIgnoreCase;
    }

    public function getTaskOwnerLike(): ?string
    {
        return $this->taskOwnerLike;
    }

    public function getTaskOwner(): ?string
    {
        return $this->taskOwner;
    }

    public function getTaskPriority(): int
    {
        return $this->taskPriority;
    }

    public function getTaskParentTaskId(): ?string
    {
        return $this->taskParentTaskId;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    /*public String getCaseDefinitionId() {
        return caseDefinitionId;
    }

    public String getCaseDefinitionKey() {
        return caseDefinitionKey;
    }

    public String getCaseDefinitionName() {
        return caseDefinitionName;
    }

    public String getCaseInstanceId() {
        return caseInstanceId;
    }

    public function getCaseExecutionId(): ?string
    {
        return caseExecutionId;
    }*/

    public function getFinishedAfter(): ?string
    {
        return $this->finishedAfter;
    }

    public function getFinishedBefore(): ?string
    {
        return $this->finishedBefore;
    }

    public function getStartedAfter(): ?string
    {
        return $this->startedAfter;
    }

    public function getStartedBefore(): ?string
    {
        return $this->startedBefore;
    }

    public function isTenantIdSet(): bool
    {
        return $this->isTenantIdSet;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function isOrQueryActive(): bool
    {
        return $this->isOrQueryActive;
    }

    public function addOrQuery(HistoricTaskInstanceQueryImpl $orQuery): void
    {
        $orQuery->isOrQueryActive = true;
        $this->queries[] = $orQuery;
    }

    public function setOrQueryActive(): void
    {
        $this->isOrQueryActive = true;
    }

    public function or(): HistoricTaskInstanceQueryInterface
    {
        if ($this != $this->queries[0]) {
            throw new ProcessEngineException("Invalid query usage: cannot set or() within 'or' query");
        }

        $orQuery = new HistoricTaskInstanceQueryImpl();
        $orQuery->isOrQueryActive = true;
        $orQuery->queries = $this->queries;
        $this->queries[] = $orQuery;
        return $orQuery;
    }

    public function endOr(): HistoricTaskInstanceQueryInterface
    {
        if (!empty($this->queries) && $this != $this->queries[count($this->queries) - 1]) {
            throw new ProcessEngineException("Invalid query usage: cannot set endOr() before or()");
        }
        return $this->queries[0];
    }
}
