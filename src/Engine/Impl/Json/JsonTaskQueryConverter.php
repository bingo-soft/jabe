<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\{
    QueryOperator,
    QueryOrderingProperty,
    TaskQueryImpl,
    TaskQueryVariableValue
};
use Jabe\Engine\Impl\Persistence\Entity\SuspensionState;
use Jabe\Engine\Impl\Util\JsonUtil;
use Jabe\Engine\Task\{
    DelegationState,
    TaskQueryInterface
};

class JsonTaskQueryConverter extends JsonObjectConverter
{
    public const ID = "id";
    public const TASK_ID = "taskId";
    public const TASK_ID_IN = "taskIdIn";
    public const NAME = "name";
    public const NAME_NOT_EQUAL = "nameNotEqual";
    public const NAME_LIKE = "nameLike";
    public const NAME_NOT_LIKE = "nameNotLike";
    public const DESCRIPTION = "description";
    public const DESCRIPTION_LIKE = "descriptionLike";
    public const PRIORITY = "priority";
    public const MIN_PRIORITY = "minPriority";
    public const MAX_PRIORITY = "maxPriority";
    public const ASSIGNEE = "assignee";
    public const ASSIGNEE_LIKE = "assigneeLike";
    public const ASSIGNEE_IN = "assigneeIn";
    public const ASSIGNEE_NOT_IN = "assigneeNotIn";
    public const INVOLVED_USER = "involvedUser";
    public const OWNER = "owner";
    public const UNASSIGNED = "unassigned";
    public const ASSIGNED = "assigned";
    public const DELEGATION_STATE = "delegationState";
    public const CANDIDATE_USER = "candidateUser";
    public const CANDIDATE_GROUP = "candidateGroup";
    public const CANDIDATE_GROUPS = "candidateGroups";
    public const WITH_CANDIDATE_GROUPS = "withCandidateGroups";
    public const WITHOUT_CANDIDATE_GROUPS = "withoutCandidateGroups";
    public const WITH_CANDIDATE_USERS = "withCandidateUsers";
    public const WITHOUT_CANDIDATE_USERS = "withoutCandidateUsers";
    public const INCLUDE_ASSIGNED_TASKS = "includeAssignedTasks";
    public const INSTANCE_ID = "instanceId";
    public const PROCESS_INSTANCE_ID = "processInstanceId";
    public const PROCESS_INSTANCE_ID_IN = "processInstanceIdIn";
    public const EXECUTION_ID = "executionId";
    public const ACTIVITY_INSTANCE_ID_IN = "activityInstanceIdIn";
    public const CREATED = "created";
    public const CREATED_BEFORE = "createdBefore";
    public const CREATED_AFTER = "createdAfter";
    public const UPDATED_AFTER = "updatedAfter";
    public const KEY = "key";
    public const KEYS = "keys";
    public const KEY_LIKE = "keyLike";
    public const PARENT_TASK_ID = "parentTaskId";
    public const PROCESS_DEFINITION_KEY = "processDefinitionKey";
    public const PROCESS_DEFINITION_KEYS = "processDefinitionKeys";
    public const PROCESS_DEFINITION_ID = "processDefinitionId";
    public const PROCESS_DEFINITION_NAME = "processDefinitionName";
    public const PROCESS_DEFINITION_NAME_LIKE = "processDefinitionNameLike";
    public const PROCESS_INSTANCE_BUSINESS_KEY = "processInstanceBusinessKey";
    public const PROCESS_INSTANCE_BUSINESS_KEYS = "processInstanceBusinessKeys";
    public const PROCESS_INSTANCE_BUSINESS_KEY_LIKE = "processInstanceBusinessKeyLike";
    public const DUE = "due";
    public const DUE_DATE = "dueDate";
    public const DUE_BEFORE = "dueBefore";
    public const DUE_AFTER = "dueAfter";
    public const WITHOUT_DUE_DATE = "withoutDueDate";
    public const FOLLOW_UP = "followUp";
    public const FOLLOW_UP_DATE = "followUpDate";
    public const FOLLOW_UP_BEFORE = "followUpBefore";
    public const FOLLOW_UP_NULL_ACCEPTED = "followUpNullAccepted";
    public const FOLLOW_UP_AFTER = "followUpAfter";
    public const EXCLUDE_SUBTASKS = "excludeSubtasks";
    /*public const CASE_DEFINITION_KEY = "caseDefinitionKey";
    public const CASE_DEFINITION_ID = "caseDefinitionId";
    public const CASE_DEFINITION_NAME = "caseDefinitionName";
    public const CASE_DEFINITION_NAME_LIKE = "caseDefinitionNameLike";
    public const CASE_INSTANCE_ID = "caseInstanceId";
    public const CASE_INSTANCE_BUSINESS_KEY = "caseInstanceBusinessKey";
    public const CASE_INSTANCE_BUSINESS_KEY_LIKE = "caseInstanceBusinessKeyLike";
    public const CASE_EXECUTION_ID = "caseExecutionId";*/
    public const ACTIVE = "active";
    public const SUSPENDED = "suspended";
    public const PROCESS_VARIABLES = "processVariables";
    public const TASK_VARIABLES = "taskVariables";
    //public const CASE_INSTANCE_VARIABLES = "caseInstanceVariables";
    public const TENANT_IDS = "tenantIds";
    public const WITHOUT_TENANT_ID = "withoutTenantId";
    public const ORDERING_PROPERTIES = "orderingProperties";
    public const OR_QUERIES = "orQueries";

    //public const ORDER_BY = "orderBy";

    protected static $variableValueConverter;// = new JsonTaskQueryVariableValueConverter();

    public static function variableValueConverter(): JsonTaskQueryVariableValueConverter
    {
        if (self::$variableValueConverter === null) {
            self::$variableValueConverter = new JsonTaskQueryVariableValueConverter();
        }
        return self::$variableValueConverter;
    }

    public function toJsonObject(/*TaskQueryInterface*/$object, bool $isOrQueryActive = false): ?\stdClass
    {
        $json = JsonUtil::createObject();
        $query = $taskQuery;
        JsonUtil::addField($json, self::TASK_ID, $query->getTaskId());
        JsonUtil::addArrayField($json, self::TASK_ID_IN, $query->getTaskIdIn());
        JsonUtil::addField($json, self::NAME, $query->getName());
        JsonUtil::addField($json, self::NAME_NOT_EQUAL, $query->getNameNotEqual());
        JsonUtil::addField($json, self::NAME_LIKE, $query->getNameLike());
        JsonUtil::addField($json, self::NAME_NOT_LIKE, $query->getNameNotLike());
        JsonUtil::addField($json, self::DESCRIPTION, $query->getDescription());
        JsonUtil::addField($json, self::DESCRIPTION_LIKE, $query->getDescriptionLike());
        JsonUtil::addField($json, self::PRIORITY, $query->getPriority());
        JsonUtil::addField($json, self::MIN_PRIORITY, $query->getMinPriority());
        JsonUtil::addField($json, self::MAX_PRIORITY, $query->getMaxPriority());
        JsonUtil::addField($json, self::ASSIGNEE, $query->getAssignee());

        if ($query->getAssigneeIn() != null) {
            JsonUtil::addArrayField(
                $json,
                self::ASSIGNEE_IN,
                $query->getAssigneeIn()
            );
        }

        if ($query->getAssigneeNotIn() != null) {
            JsonUtil::addArrayField(
                $json,
                self::ASSIGNEE_NOT_IN,
                $query->getAssigneeNotIn()
            );
        }

        JsonUtil::addField($json, self::ASSIGNEE_LIKE, $query->getAssigneeLike());
        JsonUtil::addField($json, self::INVOLVED_USER, $query->getInvolvedUser());
        JsonUtil::addField($json, self::OWNER, $query->getOwner());
        JsonUtil::addDefaultField($json, self::UNASSIGNED, false, $query->isUnassigned());
        JsonUtil::addDefaultField($json, self::ASSIGNED, false, $query->isAssigned());
        JsonUtil::addField($json, self::DELEGATION_STATE, $query->getDelegationStateString());
        JsonUtil::addField($json, self::CANDIDATE_USER, $query->getCandidateUser());
        JsonUtil::addField($json, self::CANDIDATE_GROUP, $query->getCandidateGroup());
        JsonUtil::addListField($json, self::CANDIDATE_GROUPS, $query->getCandidateGroupsInternal());
        JsonUtil::addDefaultField($json, self::WITH_CANDIDATE_GROUPS, false, $query->isWithCandidateGroups());
        JsonUtil::addDefaultField($json, self::WITHOUT_CANDIDATE_GROUPS, false, $query->isWithoutCandidateGroups());
        JsonUtil::addDefaultField($json, self::WITH_CANDIDATE_USERS, false, $query->isWithCandidateUsers());
        JsonUtil::addDefaultField($json, self::WITHOUT_CANDIDATE_USERS, false, $query->isWithoutCandidateUsers());
        JsonUtil::addField($json, self::INCLUDE_ASSIGNED_TASKS, $query->isIncludeAssignedTasksInternal());
        JsonUtil::addField($json, self::PROCESS_INSTANCE_ID, $query->getProcessInstanceId());
        if ($query->getProcessInstanceIdIn() != null) {
            JsonUtil::addArrayField($json, self::PROCESS_INSTANCE_ID_IN, $query->getProcessInstanceIdIn());
        }
        JsonUtil::addField($json, self::EXECUTION_ID, $query->getExecutionId());
        JsonUtil::addArrayField($json, self::ACTIVITY_INSTANCE_ID_IN, $query->getActivityInstanceIdIn());
        JsonUtil::addDateField($json, self::CREATED, $query->getCreateTime());
        JsonUtil::addDateField($json, self::CREATED_BEFORE, $query->getCreateTimeBefore());
        JsonUtil::addDateField($json, self::CREATED_AFTER, $query->getCreateTimeAfter());
        JsonUtil::addDateField($json, self::UPDATED_AFTER, $query->getUpdatedAfter());
        JsonUtil::addField($json, self::KEY, $query->getKey());
        JsonUtil::addArrayField($json, self::KEYS, $query->getKeys());
        JsonUtil::addField($json, self::KEY_LIKE, $query->getKeyLike());
        JsonUtil::addField($json, self::PARENT_TASK_ID, $query->getParentTaskId());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_KEY, $query->getProcessDefinitionKey());
        JsonUtil::addArrayField($json, self::PROCESS_DEFINITION_KEYS, $query->getProcessDefinitionKeys());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_ID, $query->getProcessDefinitionId());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_NAME, $query->getProcessDefinitionName());
        JsonUtil::addField($json, self::PROCESS_DEFINITION_NAME_LIKE, $query->getProcessDefinitionNameLike());
        JsonUtil::addField($json, self::PROCESS_INSTANCE_BUSINESS_KEY, $query->getProcessInstanceBusinessKey());
        JsonUtil::addArrayField($json, self::PROCESS_INSTANCE_BUSINESS_KEYS, $query->getProcessInstanceBusinessKeys());
        JsonUtil::addField($json, self::PROCESS_INSTANCE_BUSINESS_KEY_LIKE, $query->getProcessInstanceBusinessKeyLike());
        $this->addVariablesFields($json, $query->getVariables());
        JsonUtil::addDateField($json, self::DUE, $query->getDueDate());
        JsonUtil::addDateField($json, self::DUE_BEFORE, $query->getDueBefore());
        JsonUtil::addDateField($json, self::DUE_AFTER, $query->getDueAfter());
        JsonUtil::addDefaultField($json, self::WITHOUT_DUE_DATE, false, $query->isWithoutDueDate());
        JsonUtil::addDateField($json, self::FOLLOW_UP, $query->getFollowUpDate());
        JsonUtil::addDateField($json, self::FOLLOW_UP_BEFORE, $query->getFollowUpBefore());
        JsonUtil::addDefaultField($json, self::FOLLOW_UP_NULL_ACCEPTED, false, $query->isFollowUpNullAccepted());
        JsonUtil::addDateField($json, self::FOLLOW_UP_AFTER, $query->getFollowUpAfter());
        JsonUtil::addDefaultField($json, self::EXCLUDE_SUBTASKS, false, $query->isExcludeSubtasks());
        $this->addSuspensionStateField($json, $query->getSuspensionState());
        JsonUtil::addField($json, self::CASE_DEFINITION_KEY, $query->getCaseDefinitionKey());
        JsonUtil::addField($json, self::CASE_DEFINITION_ID, $query->getCaseDefinitionId());
        JsonUtil::addField($json, self::CASE_DEFINITION_NAME, $query->getCaseDefinitionName());
        JsonUtil::addField($json, self::CASE_DEFINITION_NAME_LIKE, $query->getCaseDefinitionNameLike());
        //JsonUtil::addField($json, self::CASE_INSTANCE_ID, $query->getCaseInstanceId());
        JsonUtil::addField($json, self::CASE_INSTANCE_BUSINESS_KEY, $query->getCaseInstanceBusinessKey());
        JsonUtil::addField($json, self::CASE_INSTANCE_BUSINESS_KEY_LIKE, $query->getCaseInstanceBusinessKeyLike());
        JsonUtil::addField($json, self::CASE_EXECUTION_ID, $query->getCaseExecutionId());
        $this->addTenantIdFields($json, $query);

        if (count($query->getQueries()) > 1 && !$isOrQueryActive) {
            $orQueries = JsonUtil::createArray();

            foreach ($query->getQueries() as $orQuery) {
                if ($orQuery != null && $orQuery->isOrQueryActive()) {
                    $orQueries[] = $this->toJsonObject($orQuery, true);
                }
            }
            JsonUtil::addField($json, self::OR_QUERIES, $orQueries);
        }

        if ($query->getOrderingProperties() != null && !empty($query->getOrderingProperties())) {
            JsonUtil::addField(
                $json,
                self::ORDERING_PROPERTIES,
                JsonQueryOrderingPropertyConverter::arrayConverter()->toJsonArray($query->getOrderingProperties())
            );
        }

        // expressions
        foreach ($query->getExpressions() as $key => $value) {
            JsonUtil::addField($json, $key . "Expression", $value);
        }
        return $json;
    }

    protected function addSuspensionStateField($jsonObject, SuspensionState $suspensionState = null): void
    {
        if ($suspensionState != null) {
            if ($suspensionState->equals(SuspensionState::active())) {
                JsonUtil::addField($jsonObject, self::ACTIVE, true);
            } elseif ($suspensionState->equals(SuspensionState::suspended())) {
                JsonUtil::addField($jsonObject, self::SUSPENDED, true);
            }
        }
    }

    protected function addTenantIdFields($jsonObject, TaskQueryImpl $query): void
    {
        if ($query->getTenantIds() != null) {
            JsonUtil::addArrayField($jsonObject, self::TENANT_IDS, $query->getTenantIds());
        }
        if ($query->isWithoutTenantId()) {
            JsonUtil::addField($jsonObject, self::WITHOUT_TENANT_ID, true);
        }
    }

    protected function addVariablesFields($jsonObject, array $variables): void
    {
        foreach ($variables as $variable) {
            if ($variable->isProcessInstanceVariable()) {
                $this->addVariable($jsonObject, self::PROCESS_VARIABLES, $variable);
            } elseif ($variable->isLocal()) {
                $this->addVariable($jsonObject, self::TASK_VARIABLES, $variable);
            } else {
                //$this->addVariable($jsonObject, self::CASE_INSTANCE_VARIABLES, $variable);
            }
        }
    }

    protected function addVariable($jsonObject, string $variableType, TaskQueryVariableValue $variable): void
    {
        $variables = JsonUtil::getArray($jsonObject, $variableType);
        JsonUtil::addElement($variables, self::variableValueConverter(), $variable);
        JsonUtil::addField($jsonObject, $variableType, $variables);
    }

    public function toObject(\stdClass $jsonString, bool $isOrQuery = false)
    {
        $query = new TaskQueryImpl();
        if ($isOrQuery) {
            $query->setOrQueryActive();
        }
        if (property_exists($json, self::OR_QUERIES)) {
            foreach (JsonUtil::getArray($json, self::OR_QUERIES) as $jsonElement) {
                $query->addOrQuery($this->toObject(JsonUtil::getObject($jsonElement), true));
            }
        }
        if (property_exists($json, self::TASK_ID)) {
            $query->taskId(JsonUtil::getString($json, self::TASK_ID));
        }
        if (property_exists($json, self::TASK_ID_IN)) {
            $query->taskIdIn(JsonUtil::getArray($json, self::TASK_ID_IN));
        }
        if (property_exists($json, self::NAME)) {
            $query->taskName(JsonUtil::getString($json, self::NAME));
        }
        if (property_exists($json, self::NAME_NOT_EQUAL)) {
            $query->taskNameNotEqual(JsonUtil::getString($json, self::NAME_NOT_EQUAL));
        }
        if (property_exists($json, self::NAME_LIKE)) {
            $query->taskNameLike(JsonUtil::getString($json, self::NAME_LIKE));
        }
        if (property_exists($json, self::NAME_NOT_LIKE)) {
            $query->taskNameNotLike(JsonUtil::getString($json, self::NAME_NOT_LIKE));
        }
        if (property_exists($json, self::DESCRIPTION)) {
            $query->taskDescription(JsonUtil::getString($json, self::DESCRIPTION));
        }
        if (property_exists($json, self::DESCRIPTION_LIKE)) {
            $query->taskDescriptionLike(JsonUtil::getString($json, self::DESCRIPTION_LIKE));
        }
        if (property_exists($json, self::PRIORITY)) {
            $query->taskPriority(JsonUtil::getInt($json, self::PRIORITY));
        }
        if (property_exists($json, self::MIN_PRIORITY)) {
            $query->taskMinPriority(JsonUtil::getInt($json, self::MIN_PRIORITY));
        }
        if (property_exists($json, self::MAX_PRIORITY)) {
            $query->taskMaxPriority(JsonUtil::getInt($json, self::MAX_PRIORITY));
        }
        if (property_exists($json, self::ASSIGNEE)) {
            $query->taskAssignee(JsonUtil::getString($json, self::ASSIGNEE));
        }
        if (property_exists($json, self::ASSIGNEE_LIKE)) {
            $query->taskAssigneeLike(JsonUtil::getString($json, self::ASSIGNEE_LIKE));
        }
        if (property_exists($json, self::ASSIGNEE_IN)) {
            $query->taskAssigneeIn(JsonUtil::getArray($json, self::ASSIGNEE_IN));
        }
        if (property_exists($json, self::ASSIGNEE_NOT_IN)) {
            $query->taskAssigneeNotIn(JsonUtil::getArray($json, self::ASSIGNEE_NOT_IN));
        }
        if (property_exists($json, self::INVOLVED_USER)) {
            $query->taskInvolvedUser(JsonUtil::getString($json, self::INVOLVED_USER));
        }
        if (property_exists($json, self::OWNER)) {
            $query->taskOwner(JsonUtil::getString($json, self::OWNER));
        }
        if (property_exists($json, self::ASSIGNED) && JsonUtil::getBoolean($json, self::ASSIGNED)) {
            $query->taskAssigned();
        }
        if (property_exists($json, self::UNASSIGNED) && JsonUtil::getBoolean($json, self::UNASSIGNED)) {
            $query->taskUnassigned();
        }
        if (property_exists($json, self::DELEGATION_STATE)) {
            $query->taskDelegationState(constant("DelegationState::" . JsonUtil::getString($json, self::DELEGATION_STATE)));
        }
        if (property_exists($json, self::CANDIDATE_USER)) {
            $query->taskCandidateUser(JsonUtil::getString($json, self::CANDIDATE_USER));
        }
        if (property_exists($json, self::CANDIDATE_GROUP)) {
            $query->taskCandidateGroup(JsonUtil::getString($json, self::CANDIDATE_GROUP));
        }
        if (property_exists($json, self::CANDIDATE_GROUPS) && !property_exists($json, self::CANDIDATE_USER) && !property_exists($json, self::CANDIDATE_GROUP)) {
            $query->taskCandidateGroupIn(JsonUtil::getArray($json, self::CANDIDATE_GROUPS));
        }
        if (property_exists($json, self::WITH_CANDIDATE_GROUPS) && JsonUtil::getBoolean($json, self::WITH_CANDIDATE_GROUPS)) {
            $query->withCandidateGroups();
        }
        if (property_exists($json, self::WITHOUT_CANDIDATE_GROUPS) && JsonUtil::getBoolean($json, self::WITHOUT_CANDIDATE_GROUPS)) {
            $query->withoutCandidateGroups();
        }
        if (property_exists($json, self::WITH_CANDIDATE_USERS) && JsonUtil::getBoolean($json, self::WITH_CANDIDATE_USERS)) {
            $query->withCandidateUsers();
        }
        if (property_exists($json, self::WITHOUT_CANDIDATE_USERS) && JsonUtil::getBoolean($json, self::WITHOUT_CANDIDATE_USERS)) {
            $query->withoutCandidateUsers();
        }
        if (property_exists($json, self::INCLUDE_ASSIGNED_TASKS) && JsonUtil::getBoolean($json, self::INCLUDE_ASSIGNED_TASKS)) {
            $query->includeAssignedTasksInternal();
        }
        if (property_exists($json, self::PROCESS_INSTANCE_ID)) {
            $query->processInstanceId(JsonUtil::getString($json, self::PROCESS_INSTANCE_ID));
        }
        if (property_exists($json, self::PROCESS_INSTANCE_ID_IN)) {
            $query->processInstanceIdIn(JsonUtil::getArray($json, self::PROCESS_INSTANCE_ID_IN));
        }
        if (property_exists($json, self::EXECUTION_ID)) {
            $query->executionId(JsonUtil::getString($json, self::EXECUTION_ID));
        }
        if (property_exists($json, self::ACTIVITY_INSTANCE_ID_IN)) {
            $query->activityInstanceIdIn(JsonUtil::getArray($json, self::ACTIVITY_INSTANCE_ID_IN));
        }
        if (property_exists($json, self::CREATED)) {
            $query->taskCreatedOn((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::CREATED))->format('c'));
        }
        if (property_exists($json, self::CREATED_BEFORE)) {
            $query->taskCreatedBefore((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::CREATED_BEFORE))->format('c'));
        }
        if (property_exists($json, self::CREATED_AFTER)) {
            $query->taskCreatedAfter((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::CREATED_AFTER))->format('c'));
        }
        if (property_exists($json, self::UPDATED_AFTER)) {
            $query->taskUpdatedAfter((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::UPDATED_AFTER))->format('c'));
        }
        if (property_exists($json, self::KEY)) {
            $query->taskDefinitionKey(JsonUtil::getString($json, self::KEY));
        }
        if (property_exists($json, self::KEYS)) {
            $query->taskDefinitionKeyIn(JsonUtil::getArray($json, self::KEYS));
        }
        if (property_exists($json, self::KEY_LIKE)) {
            $query->taskDefinitionKeyLike(JsonUtil::getString($json, self::KEY_LIKE));
        }
        if (property_exists($json, self::PARENT_TASK_ID)) {
            $query->taskParentTaskId(JsonUtil::getString($json, self::PARENT_TASK_ID));
        }
        if (property_exists($json, self::PROCESS_DEFINITION_KEY)) {
            $query->processDefinitionKey(JsonUtil::getString($json, self::PROCESS_DEFINITION_KEY));
        }
        if (property_exists($json, self::PROCESS_DEFINITION_KEYS)) {
            $query->processDefinitionKeyIn(JsonUtil::getArray($json, self::PROCESS_DEFINITION_KEYS));
        }
        if (property_exists($json, self::PROCESS_DEFINITION_ID)) {
            $query->processDefinitionId(JsonUtil::getString($json, self::PROCESS_DEFINITION_ID));
        }
        if (property_exists($json, self::PROCESS_DEFINITION_NAME)) {
            $query->processDefinitionName(JsonUtil::getString($json, self::PROCESS_DEFINITION_NAME));
        }
        if (property_exists($json, self::PROCESS_DEFINITION_NAME_LIKE)) {
            $query->processDefinitionNameLike(JsonUtil::getString($json, self::PROCESS_DEFINITION_NAME_LIKE));
        }
        if (property_exists($json, self::PROCESS_INSTANCE_BUSINESS_KEY)) {
            $query->processInstanceBusinessKey(JsonUtil::getString($json, self::PROCESS_INSTANCE_BUSINESS_KEY));
        }
        if (property_exists($json, self::PROCESS_INSTANCE_BUSINESS_KEYS)) {
            $query->processInstanceBusinessKeyIn(JsonUtil::getArray($json, self::PROCESS_INSTANCE_BUSINESS_KEYS));
        }
        if (property_exists($json, self::PROCESS_INSTANCE_BUSINESS_KEY_LIKE)) {
            $query->processInstanceBusinessKeyLike(JsonUtil::getString($json, self::PROCESS_INSTANCE_BUSINESS_KEY_LIKE));
        }
        if (property_exists($json, self::TASK_VARIABLES)) {
            $this->addVariables($query, JsonUtil::getArray($json, self::TASK_VARIABLES), true, false);
        }
        if (property_exists($json, self::PROCESS_VARIABLES)) {
            $this->addVariables($query, JsonUtil::getArray($json, self::PROCESS_VARIABLES), false, true);
        }
        /*if (property_exists($json, self::CASE_INSTANCE_VARIABLES)) {
            addVariables(query, JsonUtil::getArray($json, self::CASE_INSTANCE_VARIABLES), false, false);
        }*/
        if (property_exists($json, self::DUE)) {
            $query->dueDate((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::DUE))->format('c'));
        }
        if (property_exists($json, self::DUE_BEFORE)) {
            $query->dueBefore((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::DUE_BEFORE))->format('c'));
        }
        if (property_exists($json, self::DUE_AFTER)) {
            $query->dueAfter((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::DUE_AFTER))->format('c'));
        }
        if (property_exists($json, self::WITHOUT_DUE_DATE)) {
            $query->withoutDueDate();
        }
        if (property_exists($json, self::FOLLOW_UP)) {
            $query->followUpDate((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::FOLLOW_UP))->format('c'));
        }
        if (property_exists($json, self::FOLLOW_UP_BEFORE)) {
            $query->followUpBefore((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::FOLLOW_UP_BEFORE))->format('c'));
        }
        if (property_exists($json, self::FOLLOW_UP_AFTER)) {
            $query->followUpAfter((new \DateTime())->setTimestamp(JsonUtil::getLong($json, self::FOLLOW_UP_AFTER))->format('c'));
        }
        if (property_exists($json, self::FOLLOW_UP_NULL_ACCEPTED)) {
            $query->setFollowUpNullAccepted(JsonUtil::getBoolean($json, self::FOLLOW_UP_NULL_ACCEPTED));
        }
        if (property_exists($json, self::EXCLUDE_SUBTASKS) && JsonUtil::getBoolean($json, self::EXCLUDE_SUBTASKS)) {
            $query->excludeSubtasks();
        }
        if (property_exists($json, self::SUSPENDED) && JsonUtil::getBoolean($json, self::SUSPENDED)) {
            $query->suspended();
        }
        if (property_exists($json, self::ACTIVE) && JsonUtil::getBoolean($json, self::ACTIVE)) {
            $query->active();
        }
        /*if (property_exists($json, self::CASE_DEFINITION_KEY)) {
            $query->caseDefinitionKey(JsonUtil::getString($json, self::CASE_DEFINITION_KEY));
        }
        if (property_exists($json, self::CASE_DEFINITION_ID)) {
            $query->caseDefinitionId(JsonUtil::getString($json, self::CASE_DEFINITION_ID));
        }
        if (property_exists($json, self::CASE_DEFINITION_NAME)) {
            $query->caseDefinitionName(JsonUtil::getString($json, self::CASE_DEFINITION_NAME));
        }
        if (property_exists($json, self::CASE_DEFINITION_NAME_LIKE)) {
            $query->caseDefinitionNameLike(JsonUtil::getString($json, self::CASE_DEFINITION_NAME_LIKE));
        }
        if (property_exists($json, self::CASE_INSTANCE_ID)) {
            $query->caseInstanceId(JsonUtil::getString($json, self::CASE_INSTANCE_ID));
        }
        if (property_exists($json, self::CASE_INSTANCE_BUSINESS_KEY)) {
            $query->caseInstanceBusinessKey(JsonUtil::getString($json, self::CASE_INSTANCE_BUSINESS_KEY));
        }
        if (property_exists($json, self::CASE_INSTANCE_BUSINESS_KEY_LIKE)) {
            $query->caseInstanceBusinessKeyLike(JsonUtil::getString($json, self::CASE_INSTANCE_BUSINESS_KEY_LIKE));
        }
        if (property_exists($json, self::CASE_EXECUTION_ID)) {
            $query->caseExecutionId(JsonUtil::getString($json, self::CASE_EXECUTION_ID));
        }*/
        if (property_exists($json, self::TENANT_IDS)) {
            $query->tenantIdIn(JsonUtil::getArray($json, self::TENANT_IDS));
        }
        if (property_exists($json, self::WITHOUT_TENANT_ID)) {
            $query->withoutTenantId();
        }
        if (property_exists($json, self::ORDERING_PROPERTIES)) {
            $jsonArray = JsonUtil::getArray($json, self::ORDERING_PROPERTIES);
            $query->setOrderingProperties(JsonQueryOrderingPropertyConverter::arrayConverter()->toObject($jsonArray));
        }

        // expressions
        foreach ($json as $key => $value) {
            if (str_ends_with($key, "Expression")) {
                $expression = JsonUtil::getString($json, self::key);
                $query->addExpression(substr($key, 0, strlen($key) - strlen("Expression")), $expression);
            }
        }

        return $query;
    }

    protected function addVariables(TaskQueryImpl $query, array $variables, bool $isTaskVariable, bool $isProcessVariable): void
    {
        foreach ($variables as $variable) {
            $variableObj = JsonUtil::getObject($variable);
            $name = JsonUtil::getString($variableObj, self::NAME);
            $rawValue = JsonUtil::getRawObject($variableObj, "value");
            $operator = constant("QueryOperator::", JsonUtil::getString($variableObj, "operator"));
            $query->addVariable($name, $rawValue, $operator, $isTaskVariable, $isProcessVariable);
        }
    }
}
