<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Query\QueryInterface;

interface HistoricTaskInstanceQueryInterface extends QueryInterface
{
    /** Only select historic task instances for the given task id. */
    public function taskId(string $taskId): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances for the given process instance. */
    public function processInstanceId(string $processInstanceId): HistoricTaskInstanceQueryInterface;

    /** Only select historic tasks for the given process instance business key */
    public function processInstanceBusinessKey(string $processInstanceBusinessKey): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic tasks for any of the given the given process instance business keys.
     */
    public function processInstanceBusinessKeyIn(array $processInstanceBusinessKeys): HistoricTaskInstanceQueryInterface;

    /** Only select historic tasks matching the given process instance business key.
     *  The syntax is that of SQL: for example usage: nameLike(%camunda%)*/
    public function processInstanceBusinessKeyLike(string $processInstanceBusinessKey): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances for the given execution. */
    public function executionId(string $executionId): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances which have one of the given activity instance ids. **/
    public function activityInstanceIdIn(array $activityInstanceIds): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances for the given process definition. */
    public function processDefinitionId(string $processDefinitionId): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a (historic) process instance
     * which has the given process definition key.
     */
    public function processDefinitionKey(string $processDefinitionKey): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a (historic) process instance
     * which has the given definition name.
     */
    public function processDefinitionName(string $processDefinitionName): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with the given task name.
     * This is the last name given to the task.
     * The query will match the names of historic task instances in a case-insensitive way.
     */
    public function taskName(string $taskName): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with a task name like the given value.
     * This is the last name given to the task.
     * The syntax that should be used is the same as in SQL, eg. %activiti%.
     * The query will match the names of historic task instances in a case-insensitive way.
     */
    public function taskNameLike(string $taskNameLike): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with the given task description.
     * This is the last description given to the task.
     * The query will match the descriptions of historic task instances in a case-insensitive way.
     */
    public function taskDescription(string $taskDescription): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with a task description like the given value.
     * This is the last description given to the task.
     * The syntax that should be used is the same as in SQL, eg. %activiti%.
     * The query will match the descriptions of historice task instances in a case-insensitive way.
     */
    public function taskDescriptionLike(string $taskDescriptionLike): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with the given task definition key.
     * @see Task#getTaskDefinitionKey()
     */
    public function taskDefinitionKey(string $taskDefinitionKey): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with one of the given task definition keys.
     * @see Task#getTaskDefinitionKey()
     */
    public function taskDefinitionKeyIn(array $taskDefinitionKeys): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances with the given task delete reason. */
    public function taskDeleteReason(string $taskDeleteReason): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with a task description like the given value.
     * The syntax that should be used is the same as in SQL, eg. %activiti%.
     */
    public function taskDeleteReasonLike(string $taskDeleteReasonLike): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances with an assignee. */
    public function taskAssigned(): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances without an assignee. */
    public function taskUnassigned(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which were last taskAssigned to the given assignee.
     */
    public function taskAssignee(string $taskAssignee): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which were last taskAssigned to an assignee like
     * the given value.
     * The syntax that should be used is the same as in SQL, eg. %activiti%.
     */
    public function taskAssigneeLike(string $taskAssigneeLike): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have the given owner.
     */
    public function taskOwner(string $taskOwner): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have an owner like the one specified.
     * The syntax that should be used is the same as in SQL, eg. %activiti%.
     */
    public function taskOwnerLike(string $taskOwnerLike): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances with the given priority.
     */
    public function taskPriority(int $taskPriority): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are finished.
     */
    public function finished(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which aren't finished yet.
     */
    public function unfinished(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process
     * instance which is already finished.
     */
    public function processFinished(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process
     * instance which is not finished yet.
     */
    public function processUnfinished(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have mapping
     * with Historic identity links based on user id
      */
    public function taskInvolvedUser(string $involvedUser): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have mapping
     * with Historic identity links based on group id
     */
    public function taskInvolvedGroup(string $involvedGroup): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have mapping
     * with Historic identity links with the condition of user being a candidate
     */
    public function taskHadCandidateUser(string $candidateUser): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have mapping
     * with Historic identity links with the condition of group being a candidate
     */
    public function taskHadCandidateGroup(string $candidateGroup): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances which have a candidate group */
    public function withCandidateGroups(): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances which have no candidate group */
    public function withoutCandidateGroups(): HistoricTaskInstanceQueryInterface;

    /**
     * The query will match the names of task and process variables in a case-insensitive way.
     */
    public function matchVariableNamesIgnoreCase(): HistoricTaskInstanceQueryInterface;

    /**
     * The query will match the values of task and process variables in a case-insensitive way.
     */
    public function matchVariableValuesIgnoreCase(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have a local task variable with the
     * given name set to the given value. Make sure history-level is configured
     * >= AUDIT when this feature is used.
     */
    public function taskVariableValueEquals(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /** Only select subtasks of the given parent task */
    public function taskParentTaskId(string $parentTaskId): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process instance which have a variable
     * with the given name set to the given value. The last variable value in the variable updates
     * ({@link HistoricDetail}) will be used, so make sure history-level is configured
     * >= AUDIT when this feature is used.
     */
    public function processVariableValueEquals(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which have a variable with the given name, but
     * with a different value than the passed value.
     * Byte-arrays and {@link Serializable} objects (which are not primitive type wrappers)
     * are not supported.
     */
    public function processVariableValueNotEquals(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and matching the given value.
     * The syntax is that of SQL: for example usage: valueLike(%value%)
     * */
    public function processVariableValueLike(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and not matching the given value.
     * The syntax is that of SQL: for example usage: valueNotLike(%value%)
     * */
    public function processVariableValueNotLike(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and a value greater than the given one.
     */
    public function processVariableValueGreaterThan(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and a value greater than or equal to the given one.
     */
    public function processVariableValueGreaterThanOrEquals(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and a value less than the given one.
     */
    public function processVariableValueLessThan(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select historic task instances which are part of a process that have a variable
     * with the given name and a value less than or equal to the given one.
     */
    public function processVariableValueLessThanOrEquals(string $variableName, $variableValue): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances with the given due date.
     */
    public function taskDueDate(string $dueDate): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances which have a due date before the given date.
     */
    public function taskDueBefore(string $dueDate): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances which have a due date after the given date.
     */
    public function taskDueAfter(string $dueDate): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances that have no due date.
     */
    public function withoutTaskDueDate(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances with the given follow-up date.
     */
    public function taskFollowUpDate(string $followUpDate): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances which have a follow-up date before the given date.
     */
    public function taskFollowUpBefore(string $followUpDate): HistoricTaskInstanceQueryInterface;

    /**
     * Only select select historic task instances which have a follow-up date after the given date.
     */
    public function taskFollowUpAfter(string $followUpDate): HistoricTaskInstanceQueryInterface;

    /** Only select historic task instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricTaskInstanceQueryInterface;

    /** Only selects historic task instances that have no tenant id. */
    public function withoutTenantId(): HistoricTaskInstanceQueryInterface;

    /**
     * Only select tasks where end time is after given date
     */
    public function finishedAfter(string $date): HistoricTaskInstanceQueryInterface;

    /**
     * Only select tasks where end time is before given date
     */
    public function finishedBefore(string $date): HistoricTaskInstanceQueryInterface;

    /**
     * Only select tasks where started after given date
     */
    public function startedAfter(string $date): HistoricTaskInstanceQueryInterface;

    /**
     * Only select tasks where started before given date
     */
    public function startedBefore(string $date): HistoricTaskInstanceQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of historic task instances without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricTaskInstanceQueryInterface;

    /** Order by task id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskId(): HistoricTaskInstanceQueryInterface;

    /**
     * Order by the historic activity instance id this task was used in
     * (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByHistoricActivityInstanceId(): HistoricTaskInstanceQueryInterface;

    /** Order by process definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricTaskInstanceQueryInterface;

    /** Order by process instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricTaskInstanceQueryInterface;

    /** Order by execution id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): HistoricTaskInstanceQueryInterface;

    /** Order by duration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricTaskInstanceDuration(): HistoricTaskInstanceQueryInterface;

    /** Order by end time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricTaskInstanceEndTime(): HistoricTaskInstanceQueryInterface;

    /** Order by start time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricActivityInstanceStartTime(): HistoricTaskInstanceQueryInterface;

    /** Order by task name (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskName(): HistoricTaskInstanceQueryInterface;

    /** Order by task description (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskDescription(): HistoricTaskInstanceQueryInterface;

    /** Order by task assignee (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskAssignee(): HistoricTaskInstanceQueryInterface;

    /** Order by task owner (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskOwner(): HistoricTaskInstanceQueryInterface;

    /** Order by task due date (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskDueDate(): HistoricTaskInstanceQueryInterface;

    /** Order by task follow-up date (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskFollowUpDate(): HistoricTaskInstanceQueryInterface;

    /** Order by task delete reason (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByDeleteReason(): HistoricTaskInstanceQueryInterface;

    /** Order by task definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskDefinitionKey(): HistoricTaskInstanceQueryInterface;

    /** Order by task priority key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByTaskPriority(): HistoricTaskInstanceQueryInterface;

    /** Order by case definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCaseDefinitionId(): HistoricTaskInstanceQueryInterface;

    /** Order by case instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCaseInstanceId(): HistoricTaskInstanceQueryInterface;

    /** Order by case execution id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCaseExecutionId(): HistoricTaskInstanceQueryInterface;

    /**
     * <p>After calling or(), a chain of several filter criteria could follow. Each filter criterion that follows or()
     * will be linked together with an OR expression until the OR query is terminated. To terminate the OR query right
     * after the last filter criterion was applied, {@link #endOr()} must be invoked.</p>
     *
     * @return an object of the type {@link HistoricTaskInstanceQuery} on which an arbitrary amount of filter criteria could be applied.
     * The several filter criteria will be linked together by an OR expression.
     *
     * @throws ProcessEngineException when or() has been invoked directly after or() or after or() and trailing filter
     * criteria. To prevent throwing this exception, {@link #endOr()} must be invoked after a chain of filter criteria to
     * mark the end of the OR query.
     * */
    public function or(): HistoricTaskInstanceQueryInterface;

    /**
     * <p>endOr() terminates an OR query on which an arbitrary amount of filter criteria were applied. To terminate the
     * OR query which has been started by invoking {@link #or()}, endOr() must be invoked. Filter criteria which are
     * applied after calling endOr() are linked together by an AND expression.</p>
     *
     * @return an object of the type {@link HistoricTaskInstanceQuery} on which an arbitrary amount of filter criteria could be applied.
     * The filter criteria will be linked together by an AND expression.
     *
     * @throws ProcessEngineException when endOr() has been invoked before {@link #or()} was invoked. To prevent throwing
     * this exception, {@link #or()} must be invoked first.
     * */
    public function endOr(): HistoricTaskInstanceQueryInterface;
}
