<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricVariableInstanceQueryInterface extends QueryInterface
{
    /** Only select the variable with the given Id
     * @param id of the variable to select
     * @return HistoricVariableInstanceQueryInterface the query object */
    public function variableId(string $id): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables with the given process instance. */
    public function processInstanceId(string $processInstanceId): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables for the given process definition */
    public function processDefinitionId(string $processDefinitionId): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables for the given process definition key */
    public function processDefinitionKey(string $processDefinitionKey): HistoricVariableInstanceQueryInterface;

    /** Only select historic case variables with the given case instance. */
    //public function caseInstanceId(string $caseInstanceId): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables with the given variable name. */
    public function variableName(string $variableName): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables where the given variable name is like. */
    public function variableNameLike(string $variableNameLike): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables which match one of the given variable types. */
    public function variableTypeIn(array $variableTypes): HistoricVariableInstanceQueryInterface;

    /** The query will match the names of task and process variables in a case-insensitive way. */
    public function matchVariableNamesIgnoreCase(): HistoricVariableInstanceQueryInterface;

    /** The query will match the values of task and process variables in a case-insensitive way. */
    public function matchVariableValuesIgnoreCase(): HistoricVariableInstanceQueryInterface;

    /** only select historic process variables with the given name and value */
    public function variableValueEquals(string $variableName, $variableValue): HistoricVariableInstanceQueryInterface;

    public function orderByProcessInstanceId(): HistoricVariableInstanceQueryInterface;

    public function orderByVariableName(): HistoricVariableInstanceQueryInterface;

    /** Only select historic process variables with the given process instance ids. */
    public function processInstanceIdIn(array $processInstanceIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances which have one of the task ids. **/
    public function taskIdIn(array $taskIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances which have one of the executions ids. **/
    public function executionIdIn(array $executionIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances which have one of the case executions ids. **/
    //public function caseExecutionIdIn(array $caseExecutionIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances with one of the given case activity ids. **/
    //public function caseActivityIdIn(array $caseActivityIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances which have one of the activity instance ids. **/
    public function activityInstanceIdIn(array $activityInstanceIds): HistoricVariableInstanceQueryInterface;

    /** Only select historic variable instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricVariableInstanceQueryInterface;

    /** Only selects historic variable instances that have no tenant id. */
    public function withoutTenantId(): HistoricVariableInstanceQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of historic variable instances without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricVariableInstanceQueryInterface;

    /**
     * Disable fetching of byte array and file values. By default, the query will fetch such values.
     * By calling this method you can prevent the values of (potentially large) blob data chunks
     * to be fetched. The variables themselves are nonetheless included in the query result.
     *
     * @return HistoricVariableInstanceQueryInterface the query builder
     */
    public function disableBinaryFetching(): HistoricVariableInstanceQueryInterface;

    /**
     * Disable deserialization of variable values that are custom objects. By default, the query
     * will attempt to deserialize the value of these variables. By calling this method you can
     * prevent such attempts in environments where their classes are not available.
     * Independent of this setting, variable serialized values are accessible.
     */
    public function disableCustomObjectDeserialization(): HistoricVariableInstanceQueryInterface;

    /**
     * Include variables that has been already deleted during the execution
     */
    public function includeDeleted(): HistoricVariableInstanceQueryInterface;
}
