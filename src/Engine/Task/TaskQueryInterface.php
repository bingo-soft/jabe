<?php

namespace BpmPlatform\Engine\Task;

use BpmPlatform\Engine\Query\QueryInterface;
use BpmPlatform\Engine\Variable\Type\ValueTypeInterface;

interface TaskQueryInterface extends QueryInterface
{
    /**
     * Only select tasks with the given task id (in practice, there will be
     * maximum one of this kind)
     */
    public function taskId(string $taskId): TaskQueryInterface;

    /** Only select tasks with the given task ids. */
    public function taskIdIn(array $taskIds): TaskQueryInterface;

    /**
     * Only select tasks with the given name.
     * The query will match the names of tasks in a case-insensitive way.
     */
    public function taskName(string $name): TaskQueryInterface;

    /**
     * Only select tasks with a name not matching the given name/
     * The query will match the names of tasks in a case-insensitive way.
     */
    public function taskNameNotEqual(string $name): TaskQueryInterface;

    /**
     * Only select tasks with a name matching the parameter.
     * The syntax is that of SQL: for example usage: nameLike(%camunda%).
     * The query will match the names of tasks in a case-insensitive way.
     */
    public function taskNameLike(string $nameLike): TaskQueryInterface;

    /**
     * Only select tasks with a name not matching the parameter.
     * The syntax is that of SQL: for example usage: nameNotLike(%camunda%)
     * The query will match the names of tasks in a case-insensitive way.
     */
    public function taskNameNotLike(string $nameNotLike): TaskQueryInterface;

    /**
     * Only select tasks with the given description.
     * The query will match the descriptions of tasks in a case-insensitive way.
     */
    public function taskDescription(string $description): TaskQueryInterface;

    /**
     * Only select tasks with a description matching the parameter .
     * The syntax is that of SQL: for example usage: descriptionLike(%camunda%)
     * The query will match the descriptions of tasks in a case-insensitive way.
     */
    public function taskDescriptionLike(string $descriptionLike): TaskQueryInterface;

    /** Only select tasks with the given priority. */
    public function taskPriority(int $priority): TaskQueryInterface;

    /** Only select tasks with the given priority or higher. */
    public function taskMinPriority(int $minPriority): TaskQueryInterface;

    /** Only select tasks with the given priority or lower. */
    public function taskMaxPriority(int $maxPriority): TaskQueryInterface;

    /** Only select tasks which are assigned to the given user. */
    public function taskAssignee(string $assignee): TaskQueryInterface;

    /**
     *  <p>Only select tasks which are assigned to the user described by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskAssigneeExpression(string $assigneeExpression): TaskQueryInterface;

    /** Only select tasks which are matching the given user.
     *  The syntax is that of SQL: for example usage: nameLike(%camunda%)*/
    public function taskAssigneeLike(string $assignee): TaskQueryInterface;

    /**
     * <p>Only select tasks which are assigned to the user described by the given expression.
     *  The syntax is that of SQL: for example usage: taskAssigneeLikeExpression("${'%test%'}")</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskAssigneeLikeExpression(string $assigneeLikeExpression): TaskQueryInterface;

    /** Only select tasks which are assigned to one of the given users. */
    public function taskAssigneeIn(array $assignees): TaskQueryInterface;

    /** Only select tasks which are not assigned to any of the given users. */
    public function taskAssigneeNotIn(array $assignees): TaskQueryInterface;

    /** Only select tasks for which the given user is the owner. */
    public function taskOwner(string $owner): TaskQueryInterface;

    /**
     * <p>Only select tasks for which the described user by the given expression is the owner.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskOwnerExpression(string $ownerExpression): TaskQueryInterface;

    /** Only select tasks which don't have an assignee. */
    public function taskUnassigned(): TaskQueryInterface;

    /** Only select tasks which have an assignee. */
    public function taskAssigned(): TaskQueryInterface;

    /** Only select tasks with the given {@link DelegationState}. */
    public function taskDelegationState(string $delegationState): TaskQueryInterface;

    /**
     * Only select tasks for which the given user or one of his groups is a candidate.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateGroup(String)} or
     *     {@link #taskCandidateGroupIn(List)} has been executed on the "and query" instance.
     *     No exception is thrown when query is executed and {@link #taskCandidateGroup(String)} or
     *     {@link #taskCandidateGroupIn(List)} has been executed on the "or query" instance.
     *   <li>When passed user is <code>null</code>.
     *   </ul>
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     *
     */
    public function taskCandidateUser(string $candidateUser): TaskQueryInterface;

    /**
     * Only select tasks for which the described user by the given expression is a candidate.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateGroup(String)} or
     *     {@link #taskCandidateGroupIn(List)} has been executed on the query instance.
     *   <li>When passed user is <code>null</code>.
     *   </ul>
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskCandidateUserExpression(string $candidateUserExpression): TaskQueryInterface;

    /** Only select tasks for which there exist an {@link IdentityLink} with the given user */
    public function taskInvolvedUser(string $involvedUser): TaskQueryInterface;

    /**
     * <p>Only select tasks for which there exist an {@link IdentityLink} with the
     * described user by the given expression</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskInvolvedUserExpression(string $involvedUserExpression): TaskQueryInterface;

    /**
     * Only select tasks which have a candidate group
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     */
    public function withCandidateGroups(): TaskQueryInterface;

    /**
     * Only select tasks which have no candidate group
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     */
    public function withoutCandidateGroups(): TaskQueryInterface;

    /**
     * Only select tasks which have a candidate user
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     */
    public function withCandidateUsers(): TaskQueryInterface;

    /**
     * Only select tasks which have no candidate user
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     */
    public function withoutCandidateUsers(): TaskQueryInterface;

    /**
     *  Only select tasks for which users in the given group are candidates.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateUser(String)} or
     *     {@link #taskCandidateGroupIn(List)} has been executed on the "and query" instance.</li>
     *   No exception is thrown when query is executed and {@link #taskCandidateUser(String)} or
     *   {@link #taskCandidateGroupIn(List)} has been executed on the "or query" instance.</li>
     *   <li>When passed group is <code>null</code>.</li></ul>
     */
    public function taskCandidateGroup(string $candidateGroup): TaskQueryInterface;

    /**
     * Only select tasks for which users in the described group by the given expression are candidates.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateUser(String)} or
     *     {@link #taskCandidateGroupIn(List)} has been executed on the query instance.
     *   <li>When passed group is <code>null</code>.
     *   </ul>
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskCandidateGroupExpression(string $candidateGroupExpression): TaskQueryInterface;

    /**
     * Only select tasks for which the 'candidateGroup' is one of the given groups.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateGroup(String)} or
     *     {@link #taskCandidateUser(String)} has been executed on the "and query" instance.</li>
     *   No exception is thrown when query is executed and {@link #taskCandidateGroup(String)} or
     *   {@link #taskCandidateUser(String)} has been executed on the "or query" instance.</li>
     *   <li>When passed group list is empty or <code>null</code>.</li></ul>
     */
    public function taskCandidateGroupIn(array $candidateGroups): TaskQueryInterface;

    /**
     * Only select tasks for which the 'candidateGroup' is one of the described groups of the given expression.
     *
     * <p>
     * Per default it only selects tasks which are not already assigned
     * to a user. To also include assigned task in the result specify
     * {@link #includeAssignedTasks()} in your query.
     * </p>
     *
     * @throws ProcessEngineException
     *   <ul><li>When query is executed and {@link #taskCandidateGroup(String)} or
     *     {@link #taskCandidateUser(String)} has been executed on the query instance.
     *   <li>When passed group list is empty or <code>null</code>.</ul>
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function taskCandidateGroupInExpression(string $candidateGroupsExpression): TaskQueryInterface;

    /**
     * Select both assigned and not assigned tasks for candidate user or group queries.
     * <p>
     * By default {@link #taskCandidateUser(String)}, {@link #taskCandidateGroup(String)}
     * and {@link #taskCandidateGroupIn(List)} queries only select not assigned tasks.
     * </p>
     *
     * @throws ProcessEngineException
     *    When no candidate user or group(s) are specified beforehand
     */
    public function includeAssignedTasks(): TaskQueryInterface;

    /** Only select tasks for the given process instance id. */
    public function processInstanceId(string $processInstanceId): TaskQueryInterface;

    /** Only select tasks for the given process instance ids. */
    public function processInstanceIdIn(array $processInstanceIds): TaskQueryInterface;

    /** Only select tasks for the given process instance business key */
    public function processInstanceBusinessKey(string $processInstanceBusinessKey): TaskQueryInterface;

    /** Only select tasks for the given process instance business key described by the given expression */
    public function processInstanceBusinessKeyExpression(string $processInstanceBusinessKeyExpression): TaskQueryInterface;

    /**
     * Only select tasks for any of the given the given process instance business keys.
     */
    public function processInstanceBusinessKeyIn(array $processInstanceBusinessKeys): TaskQueryInterface;

    /** Only select tasks matching the given process instance business key.
     *  The syntax is that of SQL: for example usage: nameLike(%camunda%)*/
    public function processInstanceBusinessKeyLike(string $processInstanceBusinessKey): TaskQueryInterface;

    /** Only select tasks matching the given process instance business key described by the given expression.
     *  The syntax is that of SQL: for example usage: processInstanceBusinessKeyLikeExpression("${ '%camunda%' }")*/
    public function processInstanceBusinessKeyLikeExpression(string $processInstanceBusinessKeyExpression): TaskQueryInterface;

    /** Only select tasks for the given execution. */
    public function executionId(string $executionId): TaskQueryInterface;

    /** Only select task which have one of the activity instance ids. **/
    public function activityInstanceIdIn(array $activityInstanceIds): TaskQueryInterface;

    /** Only select tasks that are created on the given date. **/
    public function taskCreatedOn(string $createTime): TaskQueryInterface;

    /** Only select tasks that are created on the described date by the given expression. **/
    public function taskCreatedOnExpression(string $createTimeExpression): TaskQueryInterface;

    /** Only select tasks that are created before the given date. **/
    public function taskCreatedBefore(string $before): TaskQueryInterface;

    /** Only select tasks that are created before the described date by the given expression. **/
    public function taskCreatedBeforeExpression(string $beforeExpression): TaskQueryInterface;

    /** Only select tasks that are created after the given date. **/
    public function taskCreatedAfter(string $after): TaskQueryInterface;

    /** Only select tasks that are created after the described date by the given expression. **/
    public function taskCreatedAfterExpression(string $afterExpression): TaskQueryInterface;

    /** Only select tasks that have no parent (i.e. do not select subtasks). **/
    public function excludeSubtasks(): TaskQueryInterface;

    /**
     * Only select tasks with the given taskDefinitionKey.
     * The task definition key is the id of the userTask:
     * &lt;userTask id="xxx" .../&gt;
     **/
    public function taskDefinitionKey(string $key): TaskQueryInterface;

    /**
     * Only select tasks with a taskDefinitionKey that match the given parameter.
     *  The syntax is that of SQL: for example usage: taskDefinitionKeyLike("%camunda%").
     * The task definition key is the id of the userTask:
     * &lt;userTask id="xxx" .../&gt;
     **/
    public function taskDefinitionKeyLike(string $keyLike): TaskQueryInterface;

    /** Only select tasks which have one of the taskDefinitionKeys. **/
    public function taskDefinitionKeyIn(array $taskDefinitionKeys): TaskQueryInterface;

    /**
     * Select the tasks which are sub tasks of the given parent task.
     */
    public function taskParentTaskId(string $parentTaskId): TaskQueryInterface;

    /**
     * All queries for task-, process- and case-variables will match the variable names in a case-insensitive way.
     */
    public function matchVariableNamesIgnoreCase(): TaskQueryInterface;

    /**
     * All queries for task-, process- and case-variables will match the variable values in a case-insensitive way.
     */
    public function matchVariableValuesIgnoreCase(): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * set to the given value.
     */
    public function taskVariableValueEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name, but
     * with a different value than the passed value.
     * Byte-arrays and {@link Serializable} objects (which are not primitive type wrappers)
     * are not supported.
     */
    public function taskVariableValueNotEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * matching the given value.
     * The syntax is that of SQL: for example usage: valueLike(%value%)
     */
    public function taskVariableValueLike(string $variableName, string $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * and a value greater than the given one.
     */
    public function taskVariableValueGreaterThan(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * and a value greater than or equal to the given one.
     */
    public function taskVariableValueGreaterThanOrEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * and a value less than the given one.
     */
    public function taskVariableValueLessThan(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a local task variable with the given name
     * and a value less than or equal to the given one.
     */
    public function taskVariableValueLessThanOrEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have are part of a process that have a variable
     * with the given name set to the given value.
     */
    public function processVariableValueEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which have a variable with the given name, but
     * with a different value than the passed value.
     * Byte-arrays and {@link Serializable} objects (which are not primitive type wrappers)
     * are not supported.
     */
    public function processVariableValueNotEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and matching the given value.
     * The syntax is that of SQL: for example usage: valueLike(%value%)*/
    public function processVariableValueLike(string $variableName, string $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and not matching the given value.
     * The syntax is that of SQL: for example usage: valueNotLike(%value%)*/
    public function processVariableValueNotLike(string $variableName, string $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and a value greater than the given one.
     */
    public function processVariableValueGreaterThan(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and a value greater than or equal to the given one.
     */
    public function processVariableValueGreaterThanOrEquals(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and a value less than the given one.
     */
    public function processVariableValueLessThan(string $variableName, $variableValue): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process that have a variable
     * with the given name and a value greater than or equal to the given one.
     */
    public function processVariableValueLessThanOrEquals(string $variableName, $variableValue): TaskQueryInterface;

   /**
     * Only select tasks which are part of a process instance which has the given
     * process definition key.
     */
    public function processDefinitionKey(string $processDefinitionKey): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process instance which has one of the
     * given process definition keys.
     */
    public function processDefinitionKeyIn(array $processDefinitionKeys): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process instance which has the given
     * process definition id.
     */
    public function processDefinitionId(string $processDefinitionId): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process instance which has the given
     * process definition name.
     */
    public function processDefinitionName(string $processDefinitionName): TaskQueryInterface;

    /**
     * Only select tasks which are part of a process instance which process definition
     * name  is like the given parameter.
     * The syntax is that of SQL: for example usage: nameLike(%processDefinitionName%)*/
    public function processDefinitionNameLike(string $processDefinitionName): TaskQueryInterface;

    /**
     * Only select tasks with the given due date.
     */
    public function dueDate(string $dueDate): TaskQueryInterface;

    /**
     * <p>Only select tasks with the described due date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function dueDateExpression(string $dueDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which have a due date before the given date.
     */
    public function dueBefore(string $dueDate): TaskQueryInterface;

    /**
     * <p>Only select tasks which have a due date before the described date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function dueBeforeExpression(string $dueDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which have a due date after the given date.
     */
    public function dueAfter(string $dueDate): TaskQueryInterface;

    /**
     * <p>Only select tasks which have a due date after the described date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function dueAfterExpression(string $dueDateExpression): TaskQueryInterface;

    /**
     * Only select tasks with the given follow-up date.
     */
    public function followUpDate(string $followUpDate): TaskQueryInterface;

    /**
     * <p>Only select tasks with the described follow-up date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function followUpDateExpression(string $followUpDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which have a follow-up date before the given date.
     */
    public function followUpBefore(string $followUpDate): TaskQueryInterface;

    /**
     * <p>Only select tasks which have a follow-up date before the described date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function followUpBeforeExpression(string $followUpDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which have no follow-up date or a follow-up date before the given date.
     * Serves the typical use case "give me all tasks without follow-up or follow-up date which is already due"
     */
    public function followUpBeforeOrNotExistent(string $followUpDate): TaskQueryInterface;

    /**
     * <p>Only select tasks which have no follow-up date or a follow-up date before the described date by the given expression.
     * Serves the typical use case "give me all tasks without follow-up or follow-up date which is already due"</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function followUpBeforeOrNotExistentExpression(string $followUpDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which have a follow-up date after the given date.
     */
    public function followUpAfter(string $followUpDate): TaskQueryInterface;

    /**
     * <p>Only select tasks which have a follow-up date after the described date by the given expression.</p>
     *
     * @throws BadUserRequestException
     *   <ul><li>When the query is executed and expressions are disabled for adhoc queries
     *  (in case the query is executed via {@link #list()}, {@link #listPage(int, int)}, {@link #singleResult()}, or {@link #count()})
     *  or stored queries (in case the query is stored along with a filter).
     *  Expression evaluation can be activated by setting the process engine configuration properties
     *  <code>enableExpressionsInAdhocQueries</code> (default <code>false</code>) and
     *  <code>enableExpressionsInStoredQueries</code> (default <code>true</code>) to <code>true</code>.
     */
    public function followUpAfterExpression(string $followUpDateExpression): TaskQueryInterface;

    /**
     * Only select tasks which are suspended, because its process instance was suspended.
     */
    public function suspended(): TaskQueryInterface;

    /**
     * Only select tasks which are active (ie. not suspended)
     */
    public function active(): TaskQueryInterface;

    /**
     * If called, the form keys of the fetched tasks are initialized and
     * {@link Task#getFormKey()} will return a value (in case the task has a form key).
     *
     * @throws ProcessEngineException
     *   When method has been executed within "or query". Method must be executed on the base query.
     *
     * @return the query itself
     */
    public function initializeFormKeys(): TaskQueryInterface;

    /**
     * Only select tasks with one of the given tenant ids.
     *
     * @throws ProcessEngineException
     *   <ul>
     *     <li>When a query is executed and {@link #withoutTenantId()} has been executed on
     *         the "and query" instance. No exception is thrown when a query is executed
     *         and {@link #withoutTenantId()} has been executed on the "or query" instance.
     *     </li>
     *     <li>When a <code>null</code> tenant id is passed.</li>
     *   </ul>
     */
    public function tenantIdIn(array $tenantIds): TaskQueryInterface;

    /**
     * Only select tasks which have no tenant id.
     *
     * @throws ProcessEngineException When query is executed and {@link #tenantIdIn(String...)}
     *     has been executed on the "and query" instance. No exception is thrown when query is
     *     executed and {@link #tenantIdIn(String...)} has been executed on the "or query" instance.
     */
    public function withoutTenantId(): TaskQueryInterface;

    /**
     * Only select tasks which have no due date.
     */
    public function withoutDueDate(): TaskQueryInterface;

    // ordering ////////////////////////////////////////////////////////////

    /**
     * Order by task id (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskId(): TaskQueryInterface;

    /**
     * Order by task name (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskName(): TaskQueryInterface;

    /**
     * Order by task name case insensitive (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskNameCaseInsensitive(): TaskQueryInterface;

    /**
     * Order by description (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskDescription(): TaskQueryInterface;

    /**
     * Order by priority (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskPriority(): TaskQueryInterface;

    /**
     * Order by assignee (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskAssignee(): TaskQueryInterface;

    /**
     * Order by the time on which the tasks were created (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskCreateTime(): TaskQueryInterface;

    /**
     * Order by process instance id (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByProcessInstanceId(): TaskQueryInterface;

    /**
     * Order by execution id (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByExecutionId(): TaskQueryInterface;

    /**
     * Order by due date (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByDueDate(): TaskQueryInterface;

    /**
     * Order by follow-up date (needs to be followed by {@link #asc()} or {@link #desc()}).
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByFollowUpDate(): TaskQueryInterface;

    /**
     * Order by a process instance variable value of a certain type. Calling this method multiple times
     * specifies secondary, tertiary orderings, etc. The ordering of variables with <code>null</code>
     * values is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByProcessVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface;

    /**
     * Order by an execution variable value of a certain type. Calling this method multiple times
     * specifies secondary, tertiary orderings, etc. The ordering of variables with <code>null</code>
     * values is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByExecutionVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface;

    /**
     * Order by a task variable value of a certain type. Calling this method multiple times
     * specifies secondary, tertiary orderings, etc. The ordering of variables with <code>null</code>
     * values is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTaskVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface;

    /**
     * Order by a task variable value of a certain type. Calling this method multiple times
     * specifies secondary, tertiary orderings, etc. The ordering of variables with <code>null</code>
     * values is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByCaseExecutionVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface;

    /**
     * Order by a task variable value of a certain type. Calling this method multiple times
     * specifies secondary, tertiary orderings, etc. The ordering of variables with <code>null</code>
     * values is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByCaseInstanceVariable(string $variableName, ValueTypeInterface $valueType): TaskQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of tasks without tenant id is database-specific.
     *
     * @throws ProcessEngineException When method has been executed within "or query".
     * */
    public function orderByTenantId(): TaskQueryInterface;

    /**
     * <p>After calling or(), a chain of several filter criteria could follow. Each filter criterion that follows or()
     * will be linked together with an OR expression until the OR query is terminated. To terminate the OR query right
     * after the last filter criterion was applied, {@link #endOr()} must be invoked.</p>
     *
     * @return an object of the type {@link TaskQuery} on which an arbitrary amount of filter criteria could be applied.
     * The several filter criteria will be linked together by an OR expression.
     *
     * @throws ProcessEngineException when or() has been invoked directly after or() or after or() and trailing filter
     * criteria. To prevent throwing this exception, {@link #endOr()} must be invoked after a chain of filter criteria to
     * mark the end of the OR query.
     * */
    public function or(): TaskQueryInterface;

    /**
     * <p>endOr() terminates an OR query on which an arbitrary amount of filter criteria were applied. To terminate the
     * OR query which has been started by invoking {@link #or()}, endOr() must be invoked. Filter criteria which are
     * applied after calling endOr() are linked together by an AND expression.</p>
     *
     * @return an object of the type {@link TaskQuery} on which an arbitrary amount of filter criteria could be applied.
     * The filter criteria will be linked together by an AND expression.
     *
     * @throws ProcessEngineException when endOr() has been invoked before {@link #or()} was invoked. To prevent throwing
     * this exception, {@link #or()} must be invoked first.
     * */
    public function endOr(): TaskQueryInterface;
}
