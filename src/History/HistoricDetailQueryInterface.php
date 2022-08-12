<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricDetailQueryInterface extends QueryInterface
{
    /**
     * Only select the historic detail with the given id.
     *
     * @param id the historic detail to select
     * @return HistoricDetailQueryInterface the query builder
     */
    public function detailId(string $id): HistoricDetailQueryInterface;

    /** Only select historic variable updates with the given process instance.
     * ProcessInstance ids and HistoricProcessInstance ids match. */
    public function processInstanceId(string $processInstanceId): HistoricDetailQueryInterface;

    /** Only select historic variable updates with the given case instance.
     * CaseInstance ids and HistoricCaseInstance ids match. */
    //public function caseInstanceId(string $caseInstanceId): HistoricDetailQueryInterface;

    /** Only select historic variable updates with the given execution.
     * Note that Execution ids are not stored in the history as first class citizen,
     * only process instances are.*/
    public function executionId(string $executionId): HistoricDetailQueryInterface;

    /** Only select historic variable updates with the given case execution.
     * Note that CaseExecution ids are not stored in the history as first class citizen,
     * only case instances are.*/
    //public function caseExecutionId(string $caseExecutionId): HistoricDetailQueryInterface;

    /** Only select historic variable updates associated to the given {@link HistoricActivityInstance activity instance}.
     * @deprecated since 5.2, use {@link #activityInstanceId(String)} instead */
    public function activityId(string $activityId): HistoricDetailQueryInterface;

    /** Only select historic variable updates associated to the given {@link HistoricActivityInstance activity instance}. */
    public function activityInstanceId(string $activityInstanceId): HistoricDetailQueryInterface;

    /** Only select historic variable updates associated to the given {@link HistoricTaskInstance historic task instance}. */
    public function taskId(string $taskId): HistoricDetailQueryInterface;

    /** Only select historic variable updates associated to the given {@link HistoricVariableInstance historic variable instance}. */
    public function variableInstanceId(string $variableInstanceId): HistoricDetailQueryInterface;

    /** Only select historic process variables which match one of the given variable types. */
    public function variableTypeIn(array $variableTypes): HistoricDetailQueryInterface;

    /** Only select HistoricFormFields. */
    public function formFields(): HistoricDetailQueryInterface;

    /** Only select HistoricVariableUpdates. */
    public function variableUpdates(): HistoricDetailQueryInterface;

    /**
     * Disable fetching of byte array and file values. By default, the query will fetch such values.
     * By calling this method you can prevent the values of (potentially large) blob data chunks to be fetched.
     *  The variables themselves are nonetheless included in the query result.
     *
     * @return HistoricDetailQueryInterface the query builder
     */
    public function disableBinaryFetching(): HistoricDetailQueryInterface;

    /**
     * Disable deserialization of variable values that are custom objects. By default, the query
     * will attempt to deserialize the value of these variables. By calling this method you can
     * prevent such attempts in environments where their classes are not available.
     * Independent of this setting, variable serialized values are accessible.
     */
    public function disableCustomObjectDeserialization(): HistoricDetailQueryInterface;

    /** Exclude all task-related HistoricDetails, so only items which have no
     * task-id set will be selected. When used together with {@link #taskId(String)}, this
     * call is ignored task details are NOT excluded.
     */
    public function excludeTaskDetails(): HistoricDetailQueryInterface;

    /** Only select historic details with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricDetailQueryInterface;

    /** Only selects historic details that have no tenant id. */
    public function withoutTenantId(): HistoricDetailQueryInterface;

    /** Only select historic details with the given process instance ids. */
    public function processInstanceIdIn(array $processInstanceIds): HistoricDetailQueryInterface;

    /**
     * Select historic details related with given userOperationId.
     */
    public function userOperationId(string $userOperationId): HistoricDetailQueryInterface;

    /** Only select historic details that have occurred before the given date (inclusive). */
    public function occurredBefore(string $date): HistoricDetailQueryInterface;

    /** Only select historic details that have occurred after the given date (inclusive). */
    public function occurredAfter(string $date): HistoricDetailQueryInterface;

    /** Only select historic details that were set during the process start. */
    public function initial(): HistoricDetailQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of historic details without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricDetailQueryInterface;

    public function orderByProcessInstanceId(): HistoricDetailQueryInterface;

    public function orderByVariableName(): HistoricDetailQueryInterface;

    public function orderByFormPropertyId(): HistoricDetailQueryInterface;

    public function orderByVariableType(): HistoricDetailQueryInterface;

    public function orderByVariableRevision(): HistoricDetailQueryInterface;

    public function orderByTime(): HistoricDetailQueryInterface;

    /**
     * <p>Sort the {@link HistoricDetail historic detail events} in the order in which
     * they occurred and needs to be followed by {@link #asc()} or {@link #desc()}.</p>
     *
     * <p>The set of all {@link HistoricVariableUpdate historic variable update events} is
     * a <strong>partially ordered set</strong>. Due to this fact {@link HistoricVariableUpdate
     * historic variable update events} for two different {@link VariableInstance variable
     * instances} are <strong>incomparable</strong>. So that it is not possible to sort
     * the {@link HistoricDetail historic variable update events} for two {@link VariableInstance
     * variable instances} in the order they occurred. Just for one {@link VariableInstance variable
     * instance} the set of {@link HistoricVariableUpdate historic variable update events} can be
     * <strong>totally ordered</strong> by using {@link #variableInstanceId(String)} and {@link
     * #orderPartiallyByOccurrence()} which will return a result set ordered by its occurrence.</p>
     *
     * <p><strong>For example:</strong><br>
     * An execution variable <code>myVariable</code> will be updated multiple times:</p>
     *
     * <code>
     * runtimeService.setVariable("anExecutionId", "myVariable", 1000): HistoricDetailQueryInterface;<br>
     * execution.setVariable("myVariable", 5000): HistoricDetailQueryInterface;<br>
     * runtimeService.setVariable("anExecutionId", "myVariable", 2500): HistoricDetailQueryInterface;<br>
     * runtimeService.removeVariable("anExecutionId", "myVariable"): HistoricDetailQueryInterface;
     * </code>
     *
     * <p>As a result there exists four {@link HistoricVariableUpdate historic variable update events}.</p>
     *
     * <p>By using {@link #variableInstanceId(String)} and {@link #orderPartiallyByOccurrence()} it
     * is possible to sort the events in the order in which they occurred. The following query</p>
     *
     * <code>
     * historyService.createHistoricDetailQuery()<br>
     * &nbsp;&nbsp;.variableInstanceId("myVariableInstId")<br>
     * &nbsp;&nbsp;.orderPartiallyByOccurrence()<br>
     * &nbsp;&nbsp;.asc()<br>
     * &nbsp;&nbsp;.list()
     * </code>
     *
     * <p>will return the following totally ordered result set</p>
     *
     * <code>
     * [<br>
     * &nbsp;&nbsp;HistoricVariableUpdate[id: "myVariableInstId", variableName: "myVariable", value: 1000],<br>
     * &nbsp;&nbsp;HistoricVariableUpdate[id: "myVariableInstId", variableName: "myVariable", value: 5000],<br>
     * &nbsp;&nbsp;HistoricVariableUpdate[id: "myVariableInstId", variableName: "myVariable", value: 2500]<br>
     * &nbsp;&nbsp;HistoricVariableUpdate[id: "myVariableInstId", variableName: "myVariable", value: null]<br>
     * ]
     * </code>
     *
     * <p><strong>Note:</strong><br>
     * Please note that a {@link HistoricFormField historic form field event} can occur only once.</p>
     */
    public function orderPartiallyByOccurrence(): HistoricDetailQueryInterface;
}
