<?php

namespace Jabe\Runtime;

use Jabe\Query\QueryInterface;

interface VariableInstanceQueryInterface extends QueryInterface
{
    /** Only select the variable with the given Id
     * @param the id of the variable to select
     * @return VariableInstanceQueryInterface the query object */
    public function variableId(string $id): VariableInstanceQueryInterface;

    /** Only select variable instances which have the variable name. **/
    public function variableName(string $variableName): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the variables names. **/
    public function variableNameIn(array $variableNames): VariableInstanceQueryInterface;

    /** Only select variable instances which have the name like the assigned variable name.
     * The string $can include the wildcard character '%' to express like-strategy:
     * starts with (string%), ends with (%string) or contains (%string%).
     **/
    public function variableNameLike(string $variableNameLike): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the executions ids. **/
    public function executionIdIn(array $executionIds): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the process instance ids. **/
    public function processInstanceIdIn(array $processInstanceIds): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the case execution ids. **/
    //public function caseExecutionIdIn(array $caseExecutionIds): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the case instance ids. **/
    //public function caseInstanceIdIn(array $caseInstanceIds): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the task ids. **/
    public function taskIdIn(array $taskIds): VariableInstanceQueryInterface;

    /** Only select variable instances which are related to one of the given batch ids. **/
    public function batchIdIn(array $batchIds): VariableInstanceQueryInterface;

    /** Only select variables instances which have on of the variable scope ids. **/
    public function variableScopeIdIn(array $variableScopeIds): VariableInstanceQueryInterface;

    /** Only select variable instances which have one of the activity instance ids. **/
    public function activityInstanceIdIn(array $activityInstanceIds): VariableInstanceQueryInterface;

    /**
     * The query will match the names of variables in a case-insensitive way.<br>
     * Note: This affects all <code>variableValueXXX</code> filters:
     * <ul>
     *  <li>{@link #variableValueEquals(String, Object)}</li>
     *  <li>{@link #variableValueGreaterThan(String, Object)}</li>
     *  <li>{@link #variableValueGreaterThanOrEqual(String, Object)}</li>
     *  <li>{@link #variableValueLessThan(String, Object)}</li>
     *  <li>{@link #variableValueLessThanOrEqual(String, Object)}</li>
     *  <li>{@link #variableValueLike(String, String)}</li>
     *  <li>{@link #variableValueNotEquals(String, Object)}</li>
     * </ul>
     * It does not affect:
     * <ul>
     *  <li>{@link #variableName(String)}</li>
     *  <li>{@link #variableNameIn(String...)}</li>
     *  <li>{@link #variableNameLike(String)}</li>
     * <ul>
     */
    public function matchVariableNamesIgnoreCase(): VariableInstanceQueryInterface;

    /**
     * The query will match the values of variables in a case-insensitive way.<br>
     * Note: This affects all <code>variableValueXXX</code> filters:
     * <ul>
     *  <li>{@link #variableValueEquals(String, Object)}</li>
     *  <li>{@link #variableValueGreaterThan(String, Object)}</li>
     *  <li>{@link #variableValueGreaterThanOrEqual(String, Object)}</li>
     *  <li>{@link #variableValueLessThan(String, Object)}</li>
     *  <li>{@link #variableValueLessThanOrEqual(String, Object)}</li>
     *  <li>{@link #variableValueLike(String, String)}</li>
     *  <li>{@link #variableValueNotEquals(String, Object)}</li>
     * </ul>
     */
    public function matchVariableValuesIgnoreCase(): VariableInstanceQueryInterface;

    /**
     * Only select variables instances which have the given name and value. The type
     * of variable is determined based on the value, using types configured in
     * ProcessEngineConfiguration#getVariableSerializers().
     * Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name name of the variable, cannot be null.
     * @param value variable value, can be null.
     */
    public function variableValueEquals(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which have the given name, but
     * with a different value than the passed value.
     * Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name name of the variable, cannot be null.
     * @param value variable value, can be null.
     */
    public function variableValueNotEquals(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which value is greater than the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueGreaterThan(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which value is greater than or equal to
     * the passed value. Booleans, Byte-arrays and Serializable objects (which
     * are not primitive type wrappers) are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueGreaterThanOrEqual(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which value is less than the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueLessThan(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which value is less than or equal to the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueLessThanOrEqual(string $name, $value): VariableInstanceQueryInterface;

    /**
     * Disable fetching of byte array and file values. By default, the query will fetch such values.
     * By calling this method you can prevent the values of (potentially large) blob data chunks
     * to be fetched. The variables themselves are nonetheless included in the query result.
     *
     * @return VariableInstanceQueryInterface the query builder
     */
    public function disableBinaryFetching(): VariableInstanceQueryInterface;

    /**
     * Disable deserialization of variable values that are custom objects. By default, the query
     * will attempt to deserialize the value of these variables. By calling this method you can
     * prevent such attempts in environments where their classes are not available.
     * Independent of this setting, variable serialized values are accessible.
     */
    public function disableCustomObjectDeserialization(): VariableInstanceQueryInterface;

    /**
     * Only select variable instances which value is like the given value.
     * This be used on string $variables only.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null. The string $can include the
     * wildcard character '%' to express like-strategy:
     * starts with (string%), ends with (%string) or contains (%string%).
     */
    public function variableValueLike(string $name, string $value): VariableInstanceQueryInterface;

    /** Only select variable instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): VariableInstanceQueryInterface;

    /** Order by variable name (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByVariableName(): VariableInstanceQueryInterface;

    /** Order by variable type (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByVariableType(): VariableInstanceQueryInterface;

    /** Order by activity instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityInstanceId(): VariableInstanceQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of variable instances without tenant id is database-specific.
     */
    public function orderByTenantId(): VariableInstanceQueryInterface;
}
