<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\{
    SuspensionState,
    TaskEntity
};
use Jabe\Impl\Util\{
    CompareUtil,
    EnsureUtil
};
use Jabe\Task\{
    DelegationState,
    TaskInterface,
    TaskQueryInterface
};
use Jabe\Variable\Type\ValueTypeInterface;

class TaskQueryImpl extends AbstractQuery implements TaskQueryInterface
{
    /*
     * When adding a property filter that supports Tasklist filters,
     * the following classes need to be modified:
     *
     * <ol>
     *   <li>
     *     Update the {@code TaskQuery} interface;
     *   </li>
     *   <li>
     *     Implement the new property filter and getters/setters in {@code TaskQueryImpl};
     *   </li>
     *   <li>
     *     Add the new property filter in the engine-rest {@code TaskQueryDto} class;
     *   </li>
     *   <li>
     *     Use the new filter in the engine-rest {@code TaskQueryDto#applyFilters} method.
     *     The method is used to provide Task filtering through the Rest API endpoint;
     *   </li>
     *   <li>
     *     Initialize the new property filter in the engine-rest
     *     {@code TaskQueryDto#fromQuery} method; The method is used to create a {@code TaskQueryDto}
     *     from a serialized ("saved") Task query. This is used in Tasklist filters;
     *   </li>
     *   <li>
     *     Add the property to the {@code JsonTaskQueryConverter} class, and make sure
     *     it is included in the {@code JsonTaskQueryConverter#toJsonObject} and
     *     {@code JsonTaskQueryConverter#toObject} methods. This is used to serialize/deserialize
     *     Task queries for Tasklist filter usage.
     *   </li>
     *   <li>
     *     Tests need to be added in: {@code TaskQueryTest} for Java API coverage,
     *     {@code TaskRestServiceQueryTest} for Rest API coverage and
     *     {@code FilterTaskQueryTest} for Tasklist filter coverage.
     *   </li>
     * </ol>
     */

    protected $taskId;
    protected $taskIdIn = [];
    protected $name;
    protected $nameNotEqual;
    protected $nameLike;
    protected $nameNotLike;
    protected $description;
    protected $descriptionLike;
    protected $priority;
    protected $minPriority;
    protected $maxPriority;
    protected $assignee;
    protected $assigneeLike;
    protected $assigneeIn;
    protected $assigneeNotIn;
    protected $involvedUser;
    protected $owner;
    protected $unassigned;
    protected $assigned;
    protected $noDelegationState = false;
    protected $delegationState;
    protected $candidateUser;
    protected $candidateGroup;
    protected $candidateGroups;
    protected $withCandidateGroups;
    protected $withoutCandidateGroups;
    protected $withCandidateUsers;
    protected $withoutCandidateUsers;
    protected $includeAssignedTasks;
    protected $processInstanceId;
    protected $processInstanceIdIn = [];
    protected $executionId;
    protected $activityInstanceIdIn = [];
    protected $createTime;
    protected $createTimeBefore;
    protected $createTimeAfter;
    protected $key;
    protected $keyLike;
    protected $taskDefinitionKeys = [];
    protected $processDefinitionKey;
    protected $processDefinitionKeys = [];
    protected $processDefinitionId;
    protected $processDefinitionName;
    protected $processDefinitionNameLike;
    protected $processInstanceBusinessKey;
    protected $processInstanceBusinessKeys = [];
    protected $processInstanceBusinessKeyLike;
    protected $variables = [];
    protected $dueDate;
    protected $dueBefore;
    protected $dueAfter;
    protected $followUpDate;
    protected $followUpBefore;
    protected $followUpNullAccepted = false;
    protected $followUpAfter;
    protected $excludeSubtasks = false;
    protected $suspensionState;
    protected $initializeFormKeys = false;
    protected $taskNameCaseInsensitive = false;

    protected $variableNamesIgnoreCase;
    protected $variableValuesIgnoreCase;

    protected $parentTaskId;
    protected $isWithoutTenantId = false;
    protected $isWithoutDueDate = false;

    protected $tenantIds = [];
    // case management /////////////////////////////
    /*protected String caseDefinitionKey;
    protected String caseDefinitionId;
    protected String caseDefinitionName;
    protected String caseDefinitionNameLike;
    protected String caseInstanceId;
    protected String caseInstanceBusinessKey;
    protected String caseInstanceBusinessKeyLike;
    protected String caseExecutionId;*/

    protected $cachedCandidateGroups = [];
    protected $cachedUserGroups = [];

    // or query /////////////////////////////
    protected $queries;
    protected $isOrQueryActive = false;

    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
        $this->queries = [$this];
    }

    public function taskId(string $taskId): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Task id", "taskId", $taskId);
        $this->taskId = $taskId;
        return $this;
    }

    public function taskIdIn(array $taskIds): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("taskIds", "taskIds", $taskIds);
        $this->taskIdIn = $taskIds;
        return $this;
    }

    public function taskName(string $name): TaskQueryImpl
    {
        $this->name = $name;
        return $this;
    }

    public function taskNameLike(string $nameLike): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Task nameLike", "nameLike", $nameLike);
        $this->nameLike = $nameLike;
        return $this;
    }

    public function taskDescription(string $description): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Description", "description", $description);
        $this->description = $description;
        return $this;
    }

    public function taskDescriptionLike(string $descriptionLike): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Task descriptionLike", "descriptionLike", $descriptionLike);
        $this->descriptionLike = $descriptionLike;
        return $this;
    }

    public function taskPriority(int $priority): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Priority", "priority", $priority);
        $this->priority = $priority;
        return $this;
    }

    public function taskMinPriority(int $minPriority): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Min Priority", $minPriority);
        $this->minPriority = $minPriority;
        return $this;
    }

    public function taskMaxPriority(int $maxPriority): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Max Priority", $maxPriority);
        $this->maxPriority = $maxPriority;
        return $this;
    }

    public function taskAssignee(string $assignee): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Assignee", $assignee);
        $this->assignee = $assignee;
        unset($this->expressions["taskAssignee"]);
        return $this;
    }

    public function taskAssigneeExpression(string $assigneeExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Assignee expression", "assigneeExpression", $assigneeExpression);
        $this->expressions["taskAssignee"] = $assigneeExpression;
        return $this;
    }

    public function taskAssigneeLike(string $assignee): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Assignee", "assignee", $assignee);
        $this->assigneeLike = $assignee;
        unset($this->expressions["taskAssigneeLike"]);
        return $this;
    }

    public function taskAssigneeLikeExpression(string $assigneeLikeExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Assignee like expression", "assigneeLikeExpression", $assigneeLikeExpression);
        $this->expressions["taskAssigneeLike"] = $assigneeLikeExpression;
        return $this;
    }

    public function taskAssigneeIn(array $assignees): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Assignees", "assignees", $assignees);
        $assigneeIn = [];
        $assigneeIn = $assignees;

        $this->assigneeIn = $assigneeIn;
        unset($this->expressions["taskAssigneeIn"]);

        return $this;
    }

    public function taskAssigneeNotIn(array $assignees): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Assignees", "assignees", $assignees);

        $assigneeNotIn = [];
        $this->assigneeNotIn = $assignees;

        $this->assigneeNotIn = $assigneeNotIn;
        unset($this->expressions["taskAssigneeNotIn"]);

        return $this;
    }

    public function taskOwner(string $owner): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Owner", "owner", $owner);
        $this->owner = $owner;
        unset($this->expressions["taskOwner"]);
        return $this;
    }

    public function taskOwnerExpression(string $ownerExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Owner expression", "ownerExpression", $ownerExpression);
        $this->expressions["taskOwner"] = $ownerExpression;
        return $this;
    }

    public function taskUnassigned(): TaskQueryInterface
    {
        $this->unassigned = true;
        return $this;
    }

    public function taskAssigned(): TaskQueryInterface
    {
        $this->assigned = true;
        return $this;
    }

    public function taskDelegationState(string $delegationState): TaskQueryInterface
    {
        if ($delegationState === null) {
            $this->noDelegationState = true;
        } else {
            $this->delegationState = $delegationState;
        }
        return $this;
    }

    public function taskCandidateUser(string $candidateUser): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Candidate user", "candidateUser", $candidateUser);
        if (!$this->isOrQueryActive) {
            if ($this->candidateGroup !== null || array_key_exists("taskCandidateGroup", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateUser and candidateGroup");
            }
            if (!empty($this->candidateGroups) || array_key_exists("taskCandidateGroupIn", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateUser and candidateGroupIn");
            }
        }
        $this->candidateUser = $candidateUser;
        unset($this->expressions["taskCandidateUser"]);
        return $this;
    }

    public function taskCandidateUserExpression(string $candidateUserExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Candidate user expression", "candidateUserExpression", $candidateUserExpression);

        if ($this->candidateGroup !== null || array_key_exists("taskCandidateGroup", $this->expressions)) {
            throw new ProcessEngineException("Invalid query usage: cannot set both candidateUser and candidateGroup");
        }
        if (!empty($this->candidateGroups) || array_key_exists("taskCandidateGroupIn", $this->expressions)) {
            throw new ProcessEngineException("Invalid query usage: cannot set both candidateUser and candidateGroupIn");
        }

        $this->expressions["taskCandidateUser"] = $candidateUserExpression;
        return $this;
    }

    public function taskInvolvedUser(string $involvedUser): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Involved user", "involvedUser", $involvedUser);
        $this->involvedUser = $involvedUser;
        unset($this->expressions["taskInvolvedUser"]);
        return $this;
    }

    public function taskInvolvedUserExpression(string $involvedUserExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Involved user expression", "involvedUserExpression", $involvedUserExpression);
        $this->expressions["taskInvolvedUser"] = $involvedUserExpression;
        return $this;
    }

    public function withCandidateGroups(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withCandidateGroups() within 'or' query");
        }

        $this->withCandidateGroups = true;
        return $this;
    }

    public function withoutCandidateGroups(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withoutCandidateGroups() within 'or' query");
        }

        $this->withoutCandidateGroups = true;
        return $this;
    }

    public function withCandidateUsers(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withCandidateUsers() within 'or' query");
        }
        $this->withCandidateUsers = true;
        return $this;
    }

    public function withoutCandidateUsers(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set withoutCandidateUsers() within 'or' query");
        }
        $this->withoutCandidateUsers = true;
        return $this;
    }

    public function taskCandidateGroup(string $candidateGroup): TaskQueryImpl
    {
        EnsureUtil::ensureNotNull("Candidate group", "candidateGroup", $candidateGroup);

        if (!$this->isOrQueryActive) {
            if ($this->candidateUser !== null || array_key_exists("taskCandidateUser", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateGroup and candidateUser");
            }
        }

        $this->candidateGroup = candidateGroup;
        unset($this->expressions["taskCandidateGroup"]);
        return $this;
    }

    public function taskCandidateGroupExpression(string $candidateGroupExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Candidate group expression", "candidateGroupExpression", $candidateGroupExpression);

        if (!$this->isOrQueryActive) {
            if ($this->candidateUser !== null || array_key_exists("taskCandidateUser", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateGroup and candidateUser");
            }
        }

        $this->expressions["taskCandidateGroup"] = $candidateGroupExpression;
        return $this;
    }

    public function taskCandidateGroupIn(array $candidateGroups): TaskQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Candidate group list", "candidateGroups", $candidateGroups);

        if (!$this->isOrQueryActive) {
            if ($this->candidateUser !== null || array_key_exists("taskCandidateUser", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateGroupIn and candidateUser");
            }
        }

        $this->candidateGroups = $candidateGroups;
        unset($this->expressions["taskCandidateGroupIn"]);
        return $this;
    }

    public function taskCandidateGroupInExpression(string $candidateGroupsExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotEmpty("Candidate group list expression", "candidateGroupsExpression", $candidateGroupsExpression);

        if (!$this->isOrQueryActive) {
            if ($this->candidateUser !== null || array_key_exists("taskCandidateUser", $this->expressions)) {
                throw new ProcessEngineException("Invalid query usage: cannot set both candidateGroupIn and candidateUser");
            }
        }

        $this->expressions["taskCandidateGroupIn"] = $candidateGroupsExpression;
        return $this;
    }

    public function includeAssignedTasks(): TaskQueryInterface
    {
        if (
            $this->candidateUser === null &&
            $this->candidateGroup === null &&
            $this->candidateGroups === null &&
            !$this->isWithCandidateGroups() &&
            !$this->isWithoutCandidateGroups() &&
            !$this->isWithCandidateUsers() &&
            !$this->isWithoutCandidateUsers() &&
            !array_key_exists("taskCandidateUser", $this->expressions) &&
            !array_key_exists("taskCandidateGroup", $this->expressions) &&
            !array_key_exists("taskCandidateGroupIn", $this->expressions)
        ) {
            throw new ProcessEngineException("Invalid query usage: candidateUser, candidateGroup, candidateGroupIn, withCandidateGroups, withoutCandidateGroups, withCandidateUsers, withoutCandidateUsers has to be called before 'includeAssignedTasks'.");
        }

        $this->includeAssignedTasks = true;
        return $this;
    }

    public function includeAssignedTasksInternal(): TaskQueryInterface
    {
        $this->includeAssignedTasks = true;
        return $this;
    }

    public function processInstanceId(string $processInstanceId): TaskQueryImpl
    {
        $this->processInstanceId = $processInstanceId;
        return $this;
    }

    public function processInstanceIdIn(array $processInstanceIds): TaskQueryInterface
    {
        $this->processInstanceIdIn = $processInstanceIds;
        return $this;
    }

    public function processInstanceBusinessKey(string $processInstanceBusinessKey): TaskQueryImpl
    {
        $this->processInstanceBusinessKey = $processInstanceBusinessKey;
        unset($this->expressions["processInstanceBusinessKey"]);
        return $this;
    }

    public function processInstanceBusinessKeyExpression(string $processInstanceBusinessKeyExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceBusinessKey expression", "processInstanceBusinessKeyExpression", $processInstanceBusinessKeyExpression);
        $this->expressions["processInstanceBusinessKey"] = $processInstanceBusinessKeyExpression;
        return $this;
    }

    public function processInstanceBusinessKeyIn(array $processInstanceBusinessKeys): TaskQueryInterface
    {
        $this->processInstanceBusinessKeys = $processInstanceBusinessKeys;
        return $this;
    }

    public function processInstanceBusinessKeyLike(string $processInstanceBusinessKey): TaskQueryInterface
    {
        $this->processInstanceBusinessKeyLike = $processInstanceBusinessKey;
        unset($this->expressions["processInstanceBusinessKeyLike"]);
        return $this;
    }

    public function processInstanceBusinessKeyLikeExpression(string $processInstanceBusinessKeyLikeExpression): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("processInstanceBusinessKeyLike expression", "processInstanceBusinessKeyLikeExpression", $processInstanceBusinessKeyLikeExpression);
        $this->expressions["processInstanceBusinessKeyLike"] = $processInstanceBusinessKeyLikeExpression;
        return $this;
    }

    public function executionId(string $executionId): TaskQueryImpl
    {
        $this->executionId = $executionId;
        return $this;
    }

    public function activityInstanceIdIn(array $activityInstanceIds): TaskQueryInterface
    {
        $this->activityInstanceIdIn = $activityInstanceIds;
        return $this;
    }

    public function tenantIdIn(array $tenantIds): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("tenantIds", "tenantIds", $tenantIds);

        // The tenantIdIn filter can't be used in an AND query with
        // the withoutTenantId filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutTenantId) {
                throw new ProcessEngineException("Invalid query usage: cannot set both tenantIdIn and withoutTenantId filters.");
            }
        }

        $this->tenantIds = $tenantIds;
        return $this;
    }

    public function withoutTenantId(): TaskQueryInterface
    {
        // The tenantIdIn filter can't be used in an AND query with
        // the withoutTenantId filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if (!empty($this->tenantIds) && count($this->tenantIds) > 0) {
                throw new ProcessEngineException("Invalid query usage: cannot set both tenantIdIn and withoutTenantId filters.");
            }
        }

        $this->isWithoutTenantId = true;
        return $this;
    }

    public function taskCreatedOn(string $createTime): TaskQueryImpl
    {
        $this->createTime = $createTime;
        unset($this->expressions["taskCreatedOn"]);
        return $this;
    }

    public function taskCreatedOnExpression(string $createTimeExpression): TaskQueryInterface
    {
        $this->expressions["taskCreatedOn"] = $createTimeExpression;
        return $this;
    }

    public function taskCreatedBefore(string $before): TaskQueryInterface
    {
        $this->createTimeBefore = $before;
        unset($this->expressions["taskCreatedBefore"]);
        return $this;
    }

    public function taskCreatedBeforeExpression(string $beforeExpression): TaskQueryInterface
    {
        $this->expressions["taskCreatedBefore"] = $beforeExpression;
        return $this;
    }

    public function taskCreatedAfter(string $after): TaskQueryInterface
    {
        $this->createTimeAfter = $after;
        unset($this->expressions["taskCreatedAfter"]);
        return $this;
    }

    public function taskCreatedAfterExpression(string $afterExpression): TaskQueryInterface
    {
        $this->expressions["taskCreatedAfter"] = $afterExpression;
        return $this;
    }

    public function taskDefinitionKey(string $key): TaskQueryInterface
    {
        $this->key = $key;
        return $this;
    }

    public function taskDefinitionKeyLike(string $keyLike): TaskQueryInterface
    {
        $this->keyLike = $keyLike;
        return $this;
    }

    public function taskDefinitionKeyIn(array $taskDefinitionKeys): TaskQueryInterface
    {
        $this->taskDefinitionKeys = $taskDefinitionKeys;
        return $this;
    }

    public function taskParentTaskId(string $taskParentTaskId): TaskQueryInterface
    {
        $this->parentTaskId = $taskParentTaskId;
        return $this;
    }

    /*
    public TaskQuery caseInstanceId(string $caseInstanceId) {
      EnsureUtil::ensureNotNull("caseInstanceId", caseInstanceId);
      $this->caseInstanceId = caseInstanceId;
      return $this;
    }

    public TaskQuery caseInstanceBusinessKey(string $caseInstanceBusinessKey) {
      EnsureUtil::ensureNotNull("caseInstanceBusinessKey", caseInstanceBusinessKey);
      $this->caseInstanceBusinessKey = caseInstanceBusinessKey;
      return $this;
    }

    public TaskQuery caseInstanceBusinessKeyLike(string $caseInstanceBusinessKeyLike) {
      EnsureUtil::ensureNotNull("caseInstanceBusinessKeyLike", caseInstanceBusinessKeyLike);
      $this->caseInstanceBusinessKeyLike = caseInstanceBusinessKeyLike;
      return $this;
    }

    public TaskQuery caseExecutionId(string $caseExecutionId) {
      EnsureUtil::ensureNotNull("caseExecutionId", caseExecutionId);
      $this->caseExecutionId = caseExecutionId;
      return $this;
    }

    public TaskQuery caseDefinitionId(string $caseDefinitionId) {
      EnsureUtil::ensureNotNull("caseDefinitionId", caseDefinitionId);
      $this->caseDefinitionId = caseDefinitionId;
      return $this;
    }

    public TaskQuery caseDefinitionKey(string $caseDefinitionKey) {
      EnsureUtil::ensureNotNull("caseDefinitionKey", caseDefinitionKey);
      $this->caseDefinitionKey = caseDefinitionKey;
      return $this;
    }

    public TaskQuery caseDefinitionName(string $caseDefinitionName) {
      EnsureUtil::ensureNotNull("caseDefinitionName", caseDefinitionName);
      $this->caseDefinitionName = caseDefinitionName;
      return $this;
    }

    public TaskQuery caseDefinitionNameLike(string $caseDefinitionNameLike) {
        EnsureUtil::ensureNotNull("caseDefinitionNameLike", caseDefinitionNameLike);
        $this->caseDefinitionNameLike = caseDefinitionNameLike;
        return $this;
    }*/

    public function taskVariableValueEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, true, false);
        return $this;
    }

    public function taskVariableValueNotEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_EQUALS, true, false);
        return $this;
    }

    public function taskVariableValueLike(string $variableName, string $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LIKE, true, false);
        return $this;
    }

    public function taskVariableValueGreaterThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN, true, false);
        return $this;
    }

    public function taskVariableValueGreaterThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN_OR_EQUAL, true, false);
        return $this;
    }

    public function taskVariableValueLessThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN, true, false);
        return $this;
    }

    public function taskVariableValueLessThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN_OR_EQUAL, true, false);
        return $this;
    }

    public function processVariableValueEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, false, true);
        return $this;
    }

    public function processVariableValueNotEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_EQUALS, false, true);
        return $this;
    }

    public function processVariableValueLike(string $variableName, string $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LIKE, false, true);
        return $this;
    }

    public function processVariableValueNotLike(string $variableName, string $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_LIKE, false, true);
        return $this;
    }

    public function processVariableValueGreaterThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN, false, true);
        return $this;
    }

    public function processVariableValueGreaterThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN_OR_EQUAL, false, true);
        return $this;
    }

    public function processVariableValueLessThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN, false, true);
        return $this;
    }

    public function processVariableValueLessThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN_OR_EQUAL, false, true);
        return $this;
    }

    /*public function caseInstanceVariableValueEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::EQUALS, false, false);
        return $this;
    }

    public function caseInstanceVariableValueNotEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_EQUALS, false, false);
        return $this;
    }

    public function caseInstanceVariableValueLike(string $variableName, string $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LIKE, false, false);
        return $this;
    }

    public function caseInstanceVariableValueNotLike(string $variableName, string $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::NOT_LIKE, false, false);
        return $this;
    }

    public function caseInstanceVariableValueGreaterThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN, false, false);
        return $this;
    }

    public function caseInstanceVariableValueGreaterThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::GREATER_THAN_OR_EQUAL, false, false);
        return $this;
    }

    public function caseInstanceVariableValueLessThan(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN, false, false);
        return $this;
    }

    public function caseInstanceVariableValueLessThanOrEquals(string $variableName, $variableValue): TaskQueryInterface
    {
        $this->addVariable($variableName, $variableValue, QueryOperator::LESS_THAN_OR_EQUAL, false, false);
        return $this;
    }*/

    public function processDefinitionKey(string $processDefinitionKey): TaskQueryInterface
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function processDefinitionKeyIn(array $processDefinitionKeys): TaskQueryInterface
    {
        $this->processDefinitionKeys = $processDefinitionKeys;
        return $this;
    }

    public function processDefinitionId(string $processDefinitionId): TaskQueryInterface
    {
        $this->processDefinitionId = $processDefinitionId;
        return $this;
    }

    public function processDefinitionName(string $processDefinitionName): TaskQueryInterface
    {
        $this->processDefinitionName = $processDefinitionName;
        return $this;
    }

    public function processDefinitionNameLike(string $processDefinitionName): TaskQueryInterface
    {
        $this->processDefinitionNameLike = $processDefinitionName;
        return $this;
    }

    public function dueDate(string $dueDate): TaskQueryInterface
    {
        // The dueDate filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueDate and withoutDueDate filters.");
            }
        }

        $this->dueDate = $dueDate;
        unset($this->expressions["dueDate"]);
        return $this;
    }

    public function dueDateExpression(string $dueDateExpression): TaskQueryInterface
    {
        // The dueDateExpression filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueDateExpression and withoutDueDate filters.");
            }
        }

        $this->expressions["dueDate"] = $dueDateExpression;
        return $this;
    }

    public function dueBefore(string $dueBefore): TaskQueryInterface
    {
        // The dueBefore filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueBefore and withoutDueDate filters.");
            }
        }

        $this->dueBefore = $dueBefore;
        unset($this->expressions["dueBefore"]);
        return $this;
    }

    public function dueBeforeExpression(string $dueDate): TaskQueryInterface
    {
        // The dueBeforeExpression filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueBeforeExpression and withoutDueDate filters.");
            }
        }

        $this->expressions["dueBefore"] = $dueDate;
        return $this;
    }

    public function dueAfter(string $dueAfter): TaskQueryInterface
    {
        // The dueAfter filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueAfter and withoutDueDate filters.");
            }
        }

        $this->dueAfter = $dueAfter;
        unset($this->expressions["dueAfter"]);
        return $this;
    }

    public function dueAfterExpression(string $dueDateExpression): TaskQueryInterface
    {
        // The dueAfterExpression filter can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if ($this->isWithoutDueDate) {
                throw new ProcessEngineException("Invalid query usage: cannot set both dueAfterExpression and withoutDueDate filters.");
            }
        }

        $this->expressions["dueAfter"] = $dueDateExpression;
        return $this;
    }

    public function withoutDueDate(): TaskQueryInterface
    {
        // The due date filters can't be used in an AND query with
        // the withoutDueDate filter. They can be combined in an OR query
        if (!$this->isOrQueryActive) {
            if (
                $this->dueAfter !== null ||
                $this->dueBefore !== null ||
                $this->dueDate !== null ||
                array_key_exists("dueDate", $this->expressions) ||
                array_key_exists("dueBefore", $this->expressions) ||
                array_key_exists("dueAfter", $this->expressions)
            ) {
                throw new ProcessEngineException("Invalid query usage: cannot set both due date (equal to, before, or after) and withoutDueDate filters.");
            }
        }

        $this->isWithoutDueDate = true;
        return $this;
    }

    public function followUpDate(string $followUpDate): TaskQueryInterface
    {
        $this->followUpDate = $followUpDate;
        unset($this->expressions["followUpDate"]);
        return $this;
    }

    public function followUpDateExpression(string $followUpDateExpression): TaskQueryInterface
    {
        $this->expressions["followUpDate"] = $followUpDateExpression;
        return $this;
    }

    public function followUpBefore(string $followUpBefore): TaskQueryInterface
    {
        $this->followUpBefore = $followUpBefore;
        $this->followUpNullAccepted = false;
        unset($this->expressions["followUpBefore"]);
        return $this;
    }

    public function followUpBeforeExpression(string $followUpBeforeExpression): TaskQueryInterface
    {
        $this->followUpNullAccepted = false;
        $this->expressions["followUpBefore"] = $followUpBeforeExpression;
        return $this;
    }

    public function followUpBeforeOrNotExistent(string $followUpDate): TaskQueryInterface
    {
        $this->followUpBefore = $followUpDate;
        $this->followUpNullAccepted = true;
        unset($this->expressions["followUpBeforeOrNotExistent"]);
        return $this;
    }

    public function followUpBeforeOrNotExistentExpression(string $followUpDateExpression): TaskQueryInterface
    {
        $this->expressions["followUpBeforeOrNotExistent"] = $followUpDateExpression;
        $this->followUpNullAccepted = true;
        return $this;
    }

    public function setFollowUpNullAccepted(bool $followUpNullAccepted): void
    {
        $this->followUpNullAccepted = $followUpNullAccepted;
    }

    public function followUpAfter(string $followUpAfter): TaskQueryInterface
    {
        $this->followUpAfter = $followUpAfter;
        unset($this->expressions["followUpAfter"]);
        return $this;
    }

    public function followUpAfterExpression(string $followUpAfterExpression): TaskQueryInterface
    {
        $this->expressions["followUpAfter"] = $followUpAfterExpression;
        return $this;
    }

    public function excludeSubtasks(): TaskQueryInterface
    {
        $this->excludeSubtasks = true;
        return $this;
    }

    public function active(): TaskQueryInterface
    {
        $this->suspensionState = SuspensionState::active();
        return $this;
    }

    public function suspended(): TaskQueryInterface
    {
        $this->suspensionState = SuspensionState::suspended();
        return $this;
    }

    public function initializeFormKeys(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set initializeFormKeys() within 'or' query");
        }

        $this->initializeFormKeys = true;
        return $this;
    }

    public function taskNameCaseInsensitive(): TaskQueryInterface
    {
        $this->taskNameCaseInsensitive = true;
        return $this;
    }

    protected function hasExcludingConditions(): bool
    {
        return parent::hasExcludingConditions()
          || CompareUtil::areNotInAscendingOrder($this->minPriority, $this->priority, $this->maxPriority)
          || CompareUtil::areNotInAscendingOrder($this->dueAfter, $this->dueDate, $this->dueBefore)
          || CompareUtil::areNotInAscendingOrder($this->followUpAfter, $this->followUpDate, $this->followUpBefore)
          || CompareUtil::areNotInAscendingOrder($this->createTimeAfter, $this->createTime, $this->createTimeBefore)
          || CompareUtil::elementIsNotContainedInArray($this->key, $this->taskDefinitionKeys)
          || CompareUtil::elementIsNotContainedInArray($this->processDefinitionKey, $this->processDefinitionKeys)
          || CompareUtil::elementIsNotContainedInArray($this->processInstanceBusinessKey, $this->processInstanceBusinessKeys);
    }

    public function getCandidateGroups(): array
    {
        if (!empty($this->cachedCandidateGroups)) {
            return $this->cachedCandidateGroups;
        }

        if ($this->candidateGroup !== null && !empty($this->candidateGroups)) {
            $this->cachedCandidateGroups = $this->candidateGroups;
            if (!$this->isOrQueryActive) {
                // get intersection of candidateGroups and candidateGroup
                $this->cachedCandidateGroups = array_intersect($this->cachedCandidateGroups, [$this->candidateGroup]);
            } else {
                // get union of candidateGroups and candidateGroup
                if (!in_array($this->candidateGroup, $this->candidateGroups)) {
                    $this->cachedCandidateGroups[] = $this->candidateGroup;
                }
            }
        } elseif ($this->candidateGroup !== null) {
            $this->cachedCandidateGroups = [$this->candidateGroup];
        } elseif (!empty($this->candidateGroups)) {
            $this->cachedCandidateGroups = $this->candidateGroups;
        }

        if ($this->candidateUser !== null) {
            $groupsForCandidateUser = $this->getGroupsForCandidateUser($this->candidateUser);

            if (empty($this->cachedCandidateGroups)) {
                $this->cachedCandidateGroups = $groupsForCandidateUser;
            } else {
                foreach ($groupsForCandidateUser as $group) {
                    if (!in_array($group, $this->cachedCandidateGroups)) {
                        $this->cachedCandidateGroups[] = $group;
                    }
                }
            }
        }

        return $this->cachedCandidateGroups;
    }

    public function isWithCandidateGroups(): bool
    {
        if ($this->withCandidateGroups === null) {
            return false;
        } else {
            return $this->withCandidateGroups;
        }
    }

    public function isWithCandidateUsers(): bool
    {
        if ($this->withCandidateUsers === null) {
            return false;
        } else {
            return $this->withCandidateUsers;
        }
    }

    public function isWithCandidateGroupsInternal(): bool
    {
        return $this->withCandidateGroups;
    }

    public function isWithoutCandidateGroups(): bool
    {
        if ($this->withoutCandidateGroups === null) {
            return false;
        } else {
            return $this->withoutCandidateGroups;
        }
    }

    public function isWithoutCandidateUsers(): bool
    {
        if ($this->withoutCandidateUsers === null) {
            return false;
        } else {
            return $this->withoutCandidateUsers;
        }
    }

    public function isWithoutCandidateGroupsInternal(): bool
    {
        return $this->withoutCandidateGroups;
    }

    public function getCandidateGroupsInternal(): array
    {
        return $this->candidateGroups;
    }

    protected function getGroupsForCandidateUser(string $candidateUser): array
    {
        $cachedUserGroups = $this->getCachedUserGroups();
        if (array_key_exists($candidateUser, $cachedUserGroups)) {
            return $cachedUserGroups[$candidateUser];
        }

        $groups = Context::getCommandContext()
            ->getReadOnlyIdentityProvider()
            ->createGroupQuery()
            ->groupMember($candidateUser)
            ->list();

        $groupIds = [];
        foreach ($groups as $group) {
            $groupIds[] = $group->getId();
        }

        $this->setCachedUserGroup($candidateUser, $groupIds);

        return $groupIds;
    }

    protected function getCachedUserGroups(): array
    {
        // store and retrieve cached user groups always from the first query
        if ($this->queries[0]->cachedUserGroups === null) {
            $this->queries[0]->cachedUserGroups = [];
        }
        return $this->queries[0]->cachedUserGroups;
    }

    protected function setCachedUserGroup(string $candidateUser, array $groupIds): void
    {
        $this->getCachedUserGroups();
        $this->queries[0]->cachedUserGroups[$candidateUser] = $groupIds;
    }

    protected function ensureOrExpressionsEvaluated(): void
    {
        // skips first query as it has already been evaluated
        for ($i = 1; $i < count($this->queries); $i += 1) {
            $this->queries[$i]->validate();
            $this->queries[$i]->evaluateExpressions();
        }
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

    public function addVariable($name, $value = null, string $operator = null, bool $isTaskVariable = null, bool $isProcessInstanceVariable = null): void
    {
        if ($name instanceof TaskQueryVariableValue) {
            $this->variables[] = $name;
        } else {
            EnsureUtil::ensureNotNull("name", "name", $name);

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

            $shouldMatchVariableValuesIgnoreCase = $variableValuesIgnoreCase == true && $value !== null && is_string($value);
            $this->addVariable(new TaskQueryVariableValue($name, $value, $operator, $isTaskVariable, $isProcessInstanceVariable, $this->variableNamesIgnoreCase == true, $shouldMatchVariableValuesIgnoreCase));
        }
    }

    private function isBoolean($value = null): bool
    {
        if ($value === null) {
            return false;
        }
        return is_bool($value) || strtolower($value) == "true" || strtolower($value) == "false";
    }

    //ordering ////////////////////////////////////////////////////////////////

    public function orderByTaskId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::taskId());
    }

    public function orderByTaskName(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskName() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::name());
    }

    public function orderByTaskNameCaseInsensitive(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskNameCaseInsensitive() within 'or' query");
        }

        $this->taskNameCaseInsensitive();
        return $this->orderBy(TaskQueryProperty::nameCaseInsensitive());
    }

    public function orderByTaskDescription(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskDescription() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::description());
    }

    public function orderByTaskPriority(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskPriority() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::priority());
    }

    public function orderByProcessInstanceId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessInstanceId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::processInstanceId());
    }

    /*public function orderByCaseInstanceId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseInstanceId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::caseInstanceId());
    }*/

    public function orderByExecutionId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByExecutionId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::executionId());
    }

    public function orderByTenantId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTenantId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::tenantId());
    }

    public function orderByCaseExecutionId(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseExecutionId() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::caseExecutionId());
    }

    public function orderByTaskAssignee(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskAssignee() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::assignee());
    }

    public function orderByTaskCreateTime(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskCreateTime() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::createTime());
    }

    public function orderByDueDate(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByDueDate() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::dueDate());
    }

    public function orderByFollowUpDate(): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByFollowUpDate() within 'or' query");
        }

        return $this->orderBy(TaskQueryProperty::followUpDate());
    }

    public function orderByProcessVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByProcessVariable() within 'or' query");
        }

        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("valueType", "valueType", $valueType);

        $this->orderBy(VariableOrderProperty::forProcessInstanceVariable($variableName, $valueType));
        return $this;
    }

    public function orderByExecutionVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByExecutionVariable() within 'or' query");
        }

        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("valueType", "valueType", $valueType);

        $this->orderBy(VariableOrderProperty::forExecutionVariable($variableName, $valueType));
        return $this;
    }

    public function orderByTaskVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByTaskVariable() within 'or' query");
        }

        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("valueType", "valueType", $valueType);

        $this->orderBy(VariableOrderProperty::forTaskVariable($variableName, $valueType));
        return $this;
    }

    public function orderByCaseExecutionVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseExecutionVariable() within 'or' query");
        }

        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("valueType", "valueType", $valueType);

        $this->orderBy(VariableOrderProperty::forCaseExecutionVariable($variableName, $valueType));
        return $this;
    }

    public function orderByCaseInstanceVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface
    {
        if ($this->isOrQueryActive) {
            throw new ProcessEngineException("Invalid query usage: cannot set orderByCaseInstanceVariable() within 'or' query");
        }

        EnsureUtil::ensureNotNull("variableName", "variableName", $variableName);
        EnsureUtil::ensureNotNull("valueType", "valueType", $valueType);

        $this->orderBy(VariableOrderProperty::forCaseInstanceVariable($variableName, $valueType));
        return $this;
    }

    //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->ensureOrExpressionsEvaluated();
        $this->ensureVariablesInitialized();
        $this->checkQueryOk();

        $this->resetCachedCandidateGroups();

        //check if candidateGroup and candidateGroups intersect
        if ($this->getCandidateGroup() !== null && $this->getCandidateGroupsInternal() !== null && empty($this->getCandidateGroups())) {
            return [];
        }

        $this->decideAuthorizationJoinType($commandContext);

        $taskList = $commandContext
          ->getTaskManager()
          ->findTasksByQueryCriteria($this);

        if ($this->initializeFormKeys) {
            foreach ($taskList as $task) {
                // initialize the form keys of the tasks
                $task->initializeFormKey();
            }
        }

        return $taskList;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->ensureOrExpressionsEvaluated();
        $this->ensureVariablesInitialized();
        $this->checkQueryOk();

        $this->resetCachedCandidateGroups();

        //check if candidateGroup and candidateGroups intersect
        if ($this->getCandidateGroup() !== null && $this->getCandidateGroupsInternal() !== null && empty($this->getCandidateGroups())) {
            return 0;
        }

        $this->decideAuthorizationJoinType($commandContext);

        return $commandContext
          ->getTaskManager()
          ->findTaskCountByQueryCriteria($this);
    }

    protected function decideAuthorizationJoinType(CommandContext $commandContext): void
    {
        //$cmmnEnabled = commandContext->getProcessEngineConfiguration()->isCmmnEnabled();
        $cmmnEnabled = false;
        $this->authCheck->setUseLeftJoin($cmmnEnabled);
    }

    protected function resetCachedCandidateGroups(): void
    {
        $this->cachedCandidateGroups = null;
        for ($i = 1; $i < count($this->queries); $i += 1) {
            $this->queries[$i]->cachedCandidateGroups = null;
        }
    }

    //getters ////////////////////////////////////////////////////////////////

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameNotEqual(): string
    {
        return $this->nameNotEqual;
    }

    public function getNameLike(): string
    {
        return $this->nameLike;
    }

    public function getNameNotLike(): string
    {
        return $this->nameNotLike;
    }

    public function getAssignee(): string
    {
        return $this->assignee;
    }

    public function getAssigneeLike(): string
    {
        return $this->assigneeLike;
    }

    public function getAssigneeIn(): array
    {
        return $this->assigneeIn;
    }

    public function getAssigneeNotIn(): array
    {
        return $this->assigneeNotIn;
    }

    public function getInvolvedUser(): string
    {
        return $this->involvedUser;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function isAssigned(): bool
    {
        if ($this->assigned === null) {
            return false;
        } else {
            return $this->assigned;
        }
    }

    public function isAssignedInternal(): bool
    {
        return $this->assigned;
    }

    public function isUnassigned(): bool
    {
        if ($this->unassigned === null) {
            return false;
        } else {
            return $this->unassigned;
        }
    }

    public function isUnassignedInternal(): bool
    {
        return $this->unassigned;
    }

    public function getDelegationState(): string
    {
        return $this->delegationState;
    }

    public function isNoDelegationState(): bool
    {
        return $this->noDelegationState;
    }

    public function getDelegationStateString(): ?string
    {
        return $this->delegationState;
    }

    public function getCandidateUser(): string
    {
        return $this->candidateUser;
    }

    public function getCandidateGroup(): string
    {
        return $this->candidateGroup;
    }

    public function isIncludeAssignedTasks(): bool
    {
        return !empty($this->includeAssignedTasks) ? $this->includeAssignedTasks : false;
    }

    public function isIncludeAssignedTasksInternal(): bool
    {
        return $this->includeAssignedTasks;
    }

    public function getProcessInstanceId(): string
    {
        return $this->processInstanceId;
    }

    public function getProcessInstanceIdIn(): array
    {
        return $this->processInstanceIdIn;
    }

    public function getExecutionId(): string
    {
        return $this->executionId;
    }

    public function getActivityInstanceIdIn(): array
    {
        return $this->activityInstanceIdIn;
    }

    public function getTenantIds(): array
    {
        return $this->tenantIds;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getTaskIdIn(): array
    {
        return $this->taskIdIn;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDescriptionLike(): string
    {
        return $this->descriptionLike;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getMinPriority(): int
    {
        return $this->minPriority;
    }

    public function getMaxPriority(): int
    {
        return $this->maxPriority;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function getCreateTimeBefore(): string
    {
        return $this->createTimeBefore;
    }

    public function getCreateTimeAfter(): string
    {
        return $this->createTimeAfter;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getKeys(): array
    {
        return $this->taskDefinitionKeys;
    }

    public function getKeyLike(): string
    {
        return $this->keyLike;
    }

    public function getParentTaskId(): string
    {
        return $this->parentTaskId;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getProcessDefinitionKey(): string
    {
        return $this->processDefinitionKey;
    }

    public function getProcessDefinitionKeys(): array
    {
        return $this->processDefinitionKeys;
    }

    public function getProcessDefinitionId(): string
    {
        return $this->processDefinitionId;
    }

    public function getProcessDefinitionName(): string
    {
        return $this->processDefinitionName;
    }

    public function getProcessDefinitionNameLike(): string
    {
        return $this->processDefinitionNameLike;
    }

    public function getProcessInstanceBusinessKey(): string
    {
        return $this->processInstanceBusinessKey;
    }

    public function getProcessInstanceBusinessKeys(): array
    {
        return $this->processInstanceBusinessKeys;
    }

    public function getProcessInstanceBusinessKeyLike(): string
    {
        return $this->processInstanceBusinessKeyLike;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    public function getDueBefore(): string
    {
        return $this->dueBefore;
    }

    public function getDueAfter(): string
    {
        return $this->dueAfter;
    }

    public function getFollowUpDate(): string
    {
        return $this->followUpDate;
    }

    public function getFollowUpBefore(): string
    {
        return $this->followUpBefore;
    }

    public function getFollowUpAfter(): string
    {
        return $this->followUpAfter;
    }

    public function isExcludeSubtasks(): bool
    {
        return $this->excludeSubtasks;
    }

    public function getSuspensionState(): string
    {
        return $this->suspensionState;
    }

    /*public String getCaseInstanceId() {
      return caseInstanceId;
    }

    public String getCaseInstanceBusinessKey() {
        return caseInstanceBusinessKey;
    }

    public String getCaseInstanceBusinessKeyLike() {
        return caseInstanceBusinessKeyLike;
    }

    public String getCaseExecutionId() {
        return caseExecutionId;
    }

    public String getCaseDefinitionId() {
        return caseDefinitionId;
    }

    public String getCaseDefinitionKey() {
        return caseDefinitionKey;
    }

    public String getCaseDefinitionName() {
        return caseDefinitionName;
    }*/

    /*public function getCaseDefinitionNameLike(): string
    {
        return $this->caseDefinitionNameLike;
    }*/

    public function isInitializeFormKeys(): bool
    {
        return $this->initializeFormKeys;
    }

    public function isTaskNameCaseInsensitive(): bool
    {
        return $this->taskNameCaseInsensitive;
    }

    public function isWithoutTenantId(): bool
    {
        return $this->isWithoutTenantId;
    }

    public function isWithoutDueDate(): bool
    {
        return $this->isWithoutDueDate;
    }

    public function getTaskDefinitionKeys(): array
    {
        return $this->taskDefinitionKeys;
    }

    public function getIsTenantIdSet(): bool
    {
        return $this->isWithoutTenantId;
    }

    public function isVariableNamesIgnoreCase(): bool
    {
        return $this->variableNamesIgnoreCase;
    }

    public function isVariableValuesIgnoreCase(): bool
    {
        return $this->variableValuesIgnoreCase;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function isOrQueryActive(): bool
    {
        return $this->isOrQueryActive;
    }

    public function addOrQuery(TaskQueryImpl $orQuery): void
    {
        $orQuery->isOrQueryActive = true;
        $this->queries[] = $orQuery;
    }

    public function setOrQueryActive(): void
    {
        $this->isOrQueryActive = true;
    }

    public function extend(TaskQueryInterface $extending): TaskQueryInterface
    {
        $extendingQuery = $extending;
        $extendedQuery = new TaskQueryImpl();

        // only add the base query's validators to the new query;
        // this is because the extending query's validators may not be applicable to the base
        // query and should therefore be executed before extending the query
        $extendedQuery->validators = $this->validators;

        if ($extendingQuery->getName() !== null) {
            $extendedQuery->taskName($extendingQuery->getName());
        } elseif ($this->getName() !== null) {
            $extendedQuery->taskName($this->getName());
        }

        if ($extendingQuery->getNameLike() !== null) {
            $extendedQuery->taskNameLike($extendingQuery->getNameLike());
        } elseif ($this->getNameLike() !== null) {
            $extendedQuery->taskNameLike($this->getNameLike());
        }

        if ($extendingQuery->getNameNotEqual() !== null) {
            $extendedQuery->taskNameNotEqual($extendingQuery->getNameNotEqual());
        } elseif ($this->getNameNotEqual() !== null) {
            $extendedQuery->taskNameNotEqual($this->getNameNotEqual());
        }

        if ($extendingQuery->getNameNotLike() !== null) {
            $extendedQuery->taskNameNotLike($extendingQuery->getNameNotLike());
        } elseif ($this->getNameNotLike() !== null) {
            $extendedQuery->taskNameNotLike($this->getNameNotLike());
        }

        if ($extendingQuery->getAssignee() !== null) {
            $extendedQuery->taskAssignee($extendingQuery->getAssignee());
        } elseif ($this->getAssignee() !== null) {
            $extendedQuery->taskAssignee($this->getAssignee());
        }

        if ($extendingQuery->getAssigneeLike() !== null) {
            $extendedQuery->taskAssigneeLike($extendingQuery->getAssigneeLike());
        } elseif ($this->getAssigneeLike() !== null) {
            $extendedQuery->taskAssigneeLike($this->getAssigneeLike());
        }

        if ($extendingQuery->getAssigneeIn() !== null) {
            $extendedQuery->taskAssigneeIn($extendingQuery->getAssigneeIn());
        } elseif ($this->getAssigneeIn() !== null) {
            $extendedQuery->taskAssigneeIn($this->getAssigneeIn());
        }
        if ($extendingQuery->getAssigneeNotIn() !== null) {
            $extendedQuery->taskAssigneeNotIn($extendingQuery->getAssigneeNotIn());
        } elseif ($this->getAssigneeNotIn() !== null) {
            $extendedQuery->taskAssigneeNotIn($this->getAssigneeNotIn());
        }

        if ($extendingQuery->getInvolvedUser() !== null) {
            $extendedQuery->taskInvolvedUser($extendingQuery->getInvolvedUser());
        } elseif ($this->getInvolvedUser() !== null) {
            $extendedQuery->taskInvolvedUser($this->getInvolvedUser());
        }

        if ($extendingQuery->getOwner() !== null) {
            $extendedQuery->taskOwner($extendingQuery->getOwner());
        } elseif ($this->getOwner() !== null) {
            $extendedQuery->taskOwner($this->getOwner());
        }

        if ($extendingQuery->isAssigned() || $this->isAssigned()) {
            $extendedQuery->taskAssigned();
        }

        if ($extendingQuery->isUnassigned() || $this->isUnassigned()) {
            $extendedQuery->taskUnassigned();
        }

        if ($extendingQuery->getDelegationState() !== null) {
            $extendedQuery->taskDelegationState($extendingQuery->getDelegationState());
        } elseif ($this->getDelegationState() !== null) {
            $extendedQuery->taskDelegationState($this->getDelegationState());
        }

        if ($extendingQuery->getCandidateUser() !== null) {
            $extendedQuery->taskCandidateUser($extendingQuery->getCandidateUser());
        } elseif ($this->getCandidateUser() !== null) {
            $extendedQuery->taskCandidateUser($this->getCandidateUser());
        }

        if ($extendingQuery->getCandidateGroup() !== null) {
            $extendedQuery->taskCandidateGroup($extendingQuery->getCandidateGroup());
        } elseif ($this->getCandidateGroup() !== null) {
            $extendedQuery->taskCandidateGroup($this->getCandidateGroup());
        }

        if ($extendingQuery->isWithCandidateGroups() || $this->isWithCandidateGroups()) {
            $extendedQuery->withCandidateGroups();
        }

        if ($extendingQuery->isWithCandidateUsers() || $this->isWithCandidateUsers()) {
            $extendedQuery->withCandidateUsers();
        }

        if ($extendingQuery->isWithoutCandidateGroups() || $this->isWithoutCandidateGroups()) {
            $extendedQuery->withoutCandidateGroups();
        }

        if ($extendingQuery->isWithoutCandidateUsers() || $this->isWithoutCandidateUsers()) {
            $extendedQuery->withoutCandidateUsers();
        }

        if ($extendingQuery->getCandidateGroupsInternal() !== null) {
            $extendedQuery->taskCandidateGroupIn($extendingQuery->getCandidateGroupsInternal());
        } elseif ($this->getCandidateGroupsInternal() !== null) {
            $extendedQuery->taskCandidateGroupIn($this->getCandidateGroupsInternal());
        }

        if ($extendingQuery->getProcessInstanceId() !== null) {
            $extendedQuery->processInstanceId($extendingQuery->getProcessInstanceId());
        } elseif ($this->getProcessInstanceId() !== null) {
            $extendedQuery->processInstanceId($this->getProcessInstanceId());
        }

        if ($extendingQuery->getProcessInstanceIdIn() !== null) {
            $extendedQuery->processInstanceIdIn($extendingQuery->getProcessInstanceIdIn());
        } elseif ($this->processInstanceIdIn() !== null) {
            $extendedQuery->processInstanceIdIn($this->getProcessInstanceIdIn());
        }

        if ($extendingQuery->getExecutionId() !== null) {
            $extendedQuery->executionId($extendingQuery->getExecutionId());
        } elseif ($this->getExecutionId() !== null) {
            $extendedQuery->executionId($this->getExecutionId());
        }

        if ($extendingQuery->getActivityInstanceIdIn() !== null) {
            $extendedQuery->activityInstanceIdIn($extendingQuery->getActivityInstanceIdIn());
        } elseif ($this->getActivityInstanceIdIn() !== null) {
            $extendedQuery->activityInstanceIdIn($this->getActivityInstanceIdIn());
        }

        if ($extendingQuery->getTaskId() !== null) {
            $extendedQuery->taskId($extendingQuery->getTaskId());
        } elseif ($this->getTaskId() !== null) {
            $extendedQuery->taskId($this->getTaskId());
        }

        if ($extendingQuery->getTaskIdIn() !== null) {
            $extendedQuery->taskIdIn($extendingQuery->getTaskIdIn());
        } elseif ($this->getTaskIdIn() !== null) {
            $extendedQuery->taskIdIn($this->getTaskIdIn());
        }

        if ($extendingQuery->getDescription() !== null) {
            $extendedQuery->taskDescription($extendingQuery->getDescription());
        } elseif ($this->getDescription() !== null) {
            $extendedQuery->taskDescription($this->getDescription());
        }

        if ($extendingQuery->getDescriptionLike() !== null) {
            $extendedQuery->taskDescriptionLike($extendingQuery->getDescriptionLike());
        } elseif ($this->getDescriptionLike() !== null) {
            $extendedQuery->taskDescriptionLike($this->getDescriptionLike());
        }

        if ($extendingQuery->getPriority() !== null) {
            $extendedQuery->taskPriority($extendingQuery->getPriority());
        } elseif ($this->getPriority() !== null) {
            $extendedQuery->taskPriority($this->getPriority());
        }

        if ($extendingQuery->getMinPriority() !== null) {
            $extendedQuery->taskMinPriority($extendingQuery->getMinPriority());
        } elseif ($this->getMinPriority() !== null) {
            $extendedQuery->taskMinPriority($this->getMinPriority());
        }

        if ($extendingQuery->getMaxPriority() !== null) {
            $extendedQuery->taskMaxPriority($extendingQuery->getMaxPriority());
        } elseif ($this->getMaxPriority() !== null) {
            $extendedQuery->taskMaxPriority($this->getMaxPriority());
        }

        if ($extendingQuery->getCreateTime() !== null) {
            $extendedQuery->taskCreatedOn($extendingQuery->getCreateTime());
        } elseif ($this->getCreateTime() !== null) {
            $extendedQuery->taskCreatedOn($this->getCreateTime());
        }

        if ($extendingQuery->getCreateTimeBefore() !== null) {
            $extendedQuery->taskCreatedBefore($extendingQuery->getCreateTimeBefore());
        } elseif ($this->getCreateTimeBefore() !== null) {
            $extendedQuery->taskCreatedBefore($this->getCreateTimeBefore());
        }

        if ($extendingQuery->getCreateTimeAfter() !== null) {
            $extendedQuery->taskCreatedAfter($extendingQuery->getCreateTimeAfter());
        } elseif ($this->getCreateTimeAfter() !== null) {
            $extendedQuery->taskCreatedAfter($this->getCreateTimeAfter());
        }

        if ($extendingQuery->getKey() !== null) {
            $extendedQuery->taskDefinitionKey($extendingQuery->getKey());
        } elseif ($this->getKey() !== null) {
            $extendedQuery->taskDefinitionKey($this->getKey());
        }

        if ($extendingQuery->getKeyLike() !== null) {
            $extendedQuery->taskDefinitionKeyLike($extendingQuery->getKeyLike());
        } elseif ($this->getKeyLike() !== null) {
            $extendedQuery->taskDefinitionKeyLike($this->getKeyLike());
        }

        if ($extendingQuery->getKeys() !== null) {
            $extendedQuery->taskDefinitionKeyIn($extendingQuery->getKeys());
        } elseif ($this->getKeys() !== null) {
            $extendedQuery->taskDefinitionKeyIn($this->getKeys());
        }

        if ($extendingQuery->getParentTaskId() !== null) {
            $extendedQuery->taskParentTaskId($extendingQuery->getParentTaskId());
        } elseif ($this->getParentTaskId() !== null) {
            $extendedQuery->taskParentTaskId($this->getParentTaskId());
        }

        if ($extendingQuery->getProcessDefinitionKey() !== null) {
            $extendedQuery->processDefinitionKey($extendingQuery->getProcessDefinitionKey());
        } elseif ($this->getProcessDefinitionKey() !== null) {
            $extendedQuery->processDefinitionKey($this->getProcessDefinitionKey());
        }

        if ($extendingQuery->getProcessDefinitionKeys() !== null) {
            $extendedQuery->processDefinitionKeyIn($extendingQuery->getProcessDefinitionKeys());
        } elseif ($this->getProcessDefinitionKeys() !== null) {
            $extendedQuery->processDefinitionKeyIn($this->getProcessDefinitionKeys());
        }

        if ($extendingQuery->getProcessDefinitionId() !== null) {
            $extendedQuery->processDefinitionId($extendingQuery->getProcessDefinitionId());
        } elseif ($this->getProcessDefinitionId() !== null) {
            $extendedQuery->processDefinitionId($this->getProcessDefinitionId());
        }

        if ($extendingQuery->getProcessDefinitionName() !== null) {
            $extendedQuery->processDefinitionName($extendingQuery->getProcessDefinitionName());
        } elseif ($this->getProcessDefinitionName() !== null) {
            $extendedQuery->processDefinitionName($this->getProcessDefinitionName());
        }

        if ($extendingQuery->getProcessDefinitionNameLike() !== null) {
            $extendedQuery->processDefinitionNameLike($extendingQuery->getProcessDefinitionNameLike());
        } elseif ($this->getProcessDefinitionNameLike() !== null) {
            $extendedQuery->processDefinitionNameLike($this->getProcessDefinitionNameLike());
        }

        if ($extendingQuery->getProcessInstanceBusinessKey() !== null) {
            $extendedQuery->processInstanceBusinessKey($extendingQuery->getProcessInstanceBusinessKey());
        } elseif ($this->getProcessInstanceBusinessKey() !== null) {
            $extendedQuery->processInstanceBusinessKey($this->getProcessInstanceBusinessKey());
        }

        if ($extendingQuery->getProcessInstanceBusinessKeyLike() !== null) {
            $extendedQuery->processInstanceBusinessKeyLike($extendingQuery->getProcessInstanceBusinessKeyLike());
        } elseif ($this->getProcessInstanceBusinessKeyLike() !== null) {
            $extendedQuery->processInstanceBusinessKeyLike($this->getProcessInstanceBusinessKeyLike());
        }

        if ($extendingQuery->getDueDate() !== null) {
            $extendedQuery->dueDate($extendingQuery->getDueDate());
        } elseif ($this->getDueDate() !== null) {
            $extendedQuery->dueDate($this->getDueDate());
        }

        if ($extendingQuery->getDueBefore() !== null) {
            $extendedQuery->dueBefore($extendingQuery->getDueBefore());
        } elseif ($this->getDueBefore() !== null) {
            $extendedQuery->dueBefore($this->getDueBefore());
        }

        if ($extendingQuery->getDueAfter() !== null) {
            $extendedQuery->dueAfter($extendingQuery->getDueAfter());
        } elseif ($this->getDueAfter() !== null) {
            $extendedQuery->dueAfter($this->getDueAfter());
        }

        if ($extendingQuery->isWithoutDueDate() || $this->isWithoutDueDate()) {
            $extendedQuery->withoutDueDate();
        }

        if ($extendingQuery->getFollowUpDate() !== null) {
            $extendedQuery->followUpDate($extendingQuery->getFollowUpDate());
        } elseif ($this->getFollowUpDate() !== null) {
            $extendedQuery->followUpDate($this->getFollowUpDate());
        }

        if ($extendingQuery->getFollowUpBefore() !== null) {
            $extendedQuery->followUpBefore($extendingQuery->getFollowUpBefore());
        } elseif ($this->getFollowUpBefore() !== null) {
            $extendedQuery->followUpBefore($this->getFollowUpBefore());
        }

        if ($extendingQuery->getFollowUpAfter() !== null) {
            $extendedQuery->followUpAfter($extendingQuery->getFollowUpAfter());
        } elseif ($this->getFollowUpAfter() !== null) {
            $extendedQuery->followUpAfter($this->getFollowUpAfter());
        }

        if ($extendingQuery->isFollowUpNullAccepted() || $this->isFollowUpNullAccepted()) {
            $extendedQuery->setFollowUpNullAccepted(true);
        }

        if ($extendingQuery->isExcludeSubtasks() || $this->isExcludeSubtasks()) {
            $extendedQuery->excludeSubtasks();
        }

        if ($extendingQuery->getSuspensionState() !== null) {
            if ($extendingQuery->getSuspensionState() == SuspensionState::active()) {
                $extendedQuery->active();
            } elseif ($extendingQuery->getSuspensionState() == SuspensionState::suspended()) {
                $extendedQuery->suspended();
            }
        } elseif ($this->getSuspensionState() !== null) {
            if ($this->getSuspensionState() == SuspensionState::active()) {
                $extendedQuery->active();
            } elseif ($this->getSuspensionState() == SuspensionState::suspended()) {
                $extendedQuery->suspended();
            }
        }

        /*if ($extendingQuery->getCaseInstanceId() !== null) {
          $extendedQuery.caseInstanceId($extendingQuery->getCaseInstanceId());
        }
        elseif ($this->getCaseInstanceId() !== null) {
          $extendedQuery.caseInstanceId($this->getCaseInstanceId());
        }

        if ($extendingQuery->getCaseInstanceBusinessKey() !== null) {
          $extendedQuery.caseInstanceBusinessKey($extendingQuery->getCaseInstanceBusinessKey());
        }
        elseif ($this->getCaseInstanceBusinessKey() !== null) {
          $extendedQuery.caseInstanceBusinessKey($this->getCaseInstanceBusinessKey());
        }

        if (extendingQuery->getCaseInstanceBusinessKeyLike() !== null) {
          extendedQuery.caseInstanceBusinessKeyLike(extendingQuery->getCaseInstanceBusinessKeyLike());
        }
        elseif ($this->getCaseInstanceBusinessKeyLike() !== null) {
          extendedQuery.caseInstanceBusinessKeyLike($this->getCaseInstanceBusinessKeyLike());
        }

        if (extendingQuery->getCaseExecutionId() !== null) {
          extendedQuery.caseExecutionId(extendingQuery->getCaseExecutionId());
        }
        elseif ($this->getCaseExecutionId() !== null) {
          extendedQuery.caseExecutionId($this->getCaseExecutionId());
        }

        if (extendingQuery->getCaseDefinitionId() !== null) {
          extendedQuery.caseDefinitionId(extendingQuery->getCaseDefinitionId());
        }
        elseif ($this->getCaseDefinitionId() !== null) {
          extendedQuery.caseDefinitionId($this->getCaseDefinitionId());
        }

        if (extendingQuery->getCaseDefinitionKey() !== null) {
          extendedQuery.caseDefinitionKey(extendingQuery->getCaseDefinitionKey());
        }
        elseif ($this->getCaseDefinitionKey() !== null) {
          extendedQuery.caseDefinitionKey($this->getCaseDefinitionKey());
        }

        if (extendingQuery->getCaseDefinitionName() !== null) {
          extendedQuery.caseDefinitionName(extendingQuery->getCaseDefinitionName());
        }
        elseif ($this->getCaseDefinitionName() !== null) {
          extendedQuery.caseDefinitionName($this->getCaseDefinitionName());
        }

        if (extendingQuery->getCaseDefinitionNameLike() !== null) {
          extendedQuery.caseDefinitionNameLike(extendingQuery->getCaseDefinitionNameLike());
        }
        elseif ($this->getCaseDefinitionNameLike() !== null) {
          extendedQuery.caseDefinitionNameLike($this->getCaseDefinitionNameLike());
        }*/

        if ($extendingQuery->isInitializeFormKeys() || $this->isInitializeFormKeys()) {
            $extendedQuery->initializeFormKeys();
        }

        if ($extendingQuery->isTaskNameCaseInsensitive() || $this->isTaskNameCaseInsensitive()) {
            $extendedQuery->taskNameCaseInsensitive();
        }

        if ($extendingQuery->getTenantIds() !== null) {
            $extendedQuery->tenantIdIn($extendingQuery->getTenantIds());
        } elseif ($this->getTenantIds() !== null) {
            $extendedQuery->tenantIdIn($this->getTenantIds());
        }

        if ($extendingQuery->isWithoutTenantId() || $this->isWithoutTenantId()) {
            $extendedQuery->withoutTenantId();
        }

        // merge variables
        $this->mergeVariables($extendedQuery, $extendingQuery);

        // merge expressions
        $this->mergeExpressions($extendedQuery, $extendingQuery);

        // include taskAssigned tasks has to be set after expression as it asserts on already set
        // candidate properties which could be expressions
        if ($extendingQuery->isIncludeAssignedTasks() || $this->isIncludeAssignedTasks()) {
            $extendedQuery->includeAssignedTasks();
        }

        $this->mergeOrdering($extendedQuery, $extendingQuery);

        $extendedQuery->queries = [$extendedQuery];

        if (count($this->queries) > 1) {
            unset($this->queries[0]);
            $extendedQuery->queries = array_merge($extendedQuery->queries, $this->queries);
        }

        if (count($extendingQuery->queries) > 1) {
            unset($extendingQuery->queries[0]);
            $extendedQuery->queries = array_merge($extendedQuery->queries, $extendingQuery->queries);
        }

        return $extendedQuery;
    }

    /**
     * Simple implementation of variable merging. Variables are only overridden if they have the same name and are
     * in the same scope (ie are process instance, task or case execution variables).
     */
    protected function mergeVariables(TaskQueryImpl $extendedQuery, TaskQueryImpl $extendingQuery): void
    {
        $extendingVariables = $extendingQuery->getVariables();

        $extendingVariablesComparable = [];

        // set extending variables and save names for comparison of original variables
        foreach ($extendingVariables as $extendingVariable) {
            $extendedQuery->addVariable($extendingVariable);
            $extendingVariablesComparable[] = new TaskQueryVariableValueComparable($extendingVariable);
        }

        foreach ($this->getVariables() as $originalVariable) {
            if (!in_array(new TaskQueryVariableValueComparable($originalVariable), $extendingVariablesComparable)) {
                $extendedQuery->addVariable($originalVariable);
            }
        }
    }

    public function isFollowUpNullAccepted(): bool
    {
        return $this->followUpNullAccepted;
    }

    public function taskNameNotEqual(string $name): TaskQueryInterface
    {
        $this->nameNotEqual = $name;
        return $this;
    }

    public function taskNameNotLike(string $nameNotLike): TaskQueryInterface
    {
        EnsureUtil::ensureNotNull("Task nameNotLike", "nameNotLike", $nameNotLike);
        $this->nameNotLike = $nameNotLike;
        return $this;
    }

    /**
     * @return bool true if the query is not supposed to find CMMN or standalone tasks
     */
    public function isQueryForProcessTasksOnly(): bool
    {
        $engineConfiguration = Context::getProcessEngineConfiguration();
        return !$engineConfiguration->isStandaloneTasksEnabled();//!engineConfiguration->isCmmnEnabled() &&
    }

    public function or(): TaskQueryInterface
    {
        if (!empty($this->queries) && $this != $this->queries[0]) {
            throw new ProcessEngineException("Invalid query usage: cannot set or() within 'or' query");
        }

        $orQuery = new TaskQueryImpl();
        $orQuery->isOrQueryActive = true;
        $orQuery->queries = $this->queries;
        $this->queries->add($orQuery);
        return $orQuery;
    }

    public function endOr(): TaskQueryInterface
    {
        if (!empty($this->queries) && $this != $this->queries[count($this->queries) - 1]) {
            throw new ProcessEngineException("Invalid query usage: cannot set endOr() before or()");
        }

        return $this->queries[0];
    }

    public function matchVariableNamesIgnoreCase(): TaskQueryInterface
    {
        $this->variableNamesIgnoreCase = true;
        foreach ($this->variables as $variable) {
            $variable->setVariableNameIgnoreCase(true);
        }
        return $this;
    }

    public function matchVariableValuesIgnoreCase(): TaskQueryInterface
    {
        $this->variableValuesIgnoreCase = true;
        foreach ($this->variables as $variable) {
            $variable->setVariableValueIgnoreCase(true);
        }
        return $this;
    }
}
