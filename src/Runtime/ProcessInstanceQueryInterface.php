<?php

namespace Jabe\Runtime;

use Jabe\Query\QueryInterface;

interface ProcessInstanceQueryInterface extends QueryInterface
{
    /** Select the process instance with the given id */
    public function processInstanceId(?string $processInstanceId): QueryInterface;

    /** Select process instances whose id is in the given set of ids */
    public function processInstanceIds(array $processInstanceIds): QueryInterface;

    /** Select process instance with the given business key, unique for the given process definition */
    public function processInstanceBusinessKey(?string $processInstanceBusinessKey, ?string $processDefinitionKey = null): QueryInterface;

    /**
     * Select process instances with a business key like the given value.
     *
     * @param processInstanceBusinessKeyLike The ?string $can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function processInstanceBusinessKeyLike(?string $processInstanceBusinessKeyLike): QueryInterface;

    /**
     * Select the process instances which are defined by a process definition with
     * the given key.
     */
    public function processDefinitionKey(?string $processDefinitionKey): QueryInterface;

    /**
     * Select the process instances for any given process definition keys.
     */
    public function processDefinitionKeyIn(array $processDefinitionKeys): QueryInterface;

    /** Select historic process instances that don't have a process-definition of which the key is present in the given list */
    public function processDefinitionKeyNotIn(array $processDefinitionKeys): QueryInterface;

    /**
     * Selects the process instances which are defined by a process definition
     * with the given id.
     */
    public function processDefinitionId(?string $processDefinitionId): QueryInterface;

    /**
     * Selects the process instances which belong to the given deployment id.
     */
    public function deploymentId(?string $deploymentId): QueryInterface;

    /**
     * Select the process instances which are a sub process instance of the given
     * super process instance.
     */
    public function superProcessInstanceId(?string $superProcessInstanceId): QueryInterface;

    /**
     * Select the process instance that have as sub process instance the given
     * process instance. Note that there will always be maximum only <b>one</b>
     * such process instance that can be the result of this query.
     */
    public function subProcessInstanceId(?string $subProcessInstanceId): QueryInterface;

    /**
     * Selects the process instances which are associated with the given case instance id.
     */
    //public function caseInstanceId(?string $caseInstanceId): QueryInterface;

    /**
     * Select the process instances which are a sub process instance of the given
     * super case instance.
     */
    //public function superCaseInstanceId(?string $superCaseInstanceId): QueryInterface;

    /**
     * Select the process instance that has as sub case instance the given
     * case instance. Note that there will always be at most <b>one</b>
     * such process instance that can be the result of this query.
     */
    //public function subCaseInstanceId(?string $subCaseInstanceId): QueryInterface;

    /**
     * The query will match the names of process-variables in a case-insensitive way.
     */
    public function matchVariableNamesIgnoreCase(): QueryInterface;

    /**
     * The query will match the values of process-variables in a case-insensitive way.
     */
    public function matchVariableValuesIgnoreCase(): QueryInterface;

    /**
     * Only select process instances which have a global variable with the given value. The type
     * of variable is determined based on the value, using types configured in
     * ProcessEngineConfiguration#getVariableSerializers().
     * Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name name of the variable, cannot be null.
     */
    public function variableValueEquals(?string $name, $value): QueryInterface;

    /**
     * Only select process instances which have a global variable with the given name, but
     * with a different value than the passed value.
     * Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name name of the variable, cannot be null.
     */
    public function variableValueNotEquals(?string $name, $value): QueryInterface;


    /**
     * Only select process instances which have a variable value greater than the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueGreaterThan(?string $name, $value): QueryInterface;

    /**
     * Only select process instances which have a global variable value greater than or equal to
     * the passed value. Booleans, Byte-arrays and Serializable objects (which
     * are not primitive type wrappers) are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueGreaterThanOrEqual(?string $name, $value): QueryInterface;

    /**
     * Only select process instances which have a global variable value less than the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueLessThan(?string $name, $value): QueryInterface;

    /**
     * Only select process instances which have a global variable value less than or equal to the passed value.
     * Booleans, Byte-arrays and Serializable objects (which are not primitive type wrappers)
     * are not supported.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null.
     */
    public function variableValueLessThanOrEqual(?string $name, $value): QueryInterface;

    /**
     * Only select process instances which have a global variable value like the given value.
     * This be used on ?string $variables only.
     * @param name variable name, cannot be null.
     * @param value variable value, cannot be null. The ?string $can include the
     * wildcard character '%' to express like-strategy:
     * starts with (string%), ends with (%string) or contains (%string%).
     */
    public function variableValueLike(?string $name, ?string $value): QueryInterface;

    /**
     * Only selects process instances which are suspended, either because the
     * process instance itself is suspended or because the corresponding process
     * definition is suspended
     */
    public function suspended(): QueryInterface;

    /**
     * Only selects process instances which are active, which means that
     * neither the process instance nor the corresponding process definition
     * are suspended.
     */
    public function active(): QueryInterface;

    /**
     * Only selects process instances with at least one incident.
     */
    public function withIncident(): QueryInterface;

    /**
     * Only selects process instances with the given incident type.
     */
    public function incidentType(?string $incidentType): QueryInterface;

    /**
     * Only selects process instances with the given incident id.
     */
    public function incidentId(?string $incidentId): QueryInterface;

    /**
     * Only selects process instances with the given incident message.
     */
    public function incidentMessage(?string $incidentMessage): QueryInterface;

    /**
     * Only selects process instances with an incident message like the given.
     */
    public function incidentMessageLike(?string $incidentMessageLike): QueryInterface;

    /** Only select process instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): QueryInterface;

    /** Only selects process instances which have no tenant id. */
    public function withoutTenantId(): QueryInterface;

    /**
     * <p>Only selects process instances with leaf activity instances
     * or transition instances (async before, async after) in
     * at least one of the given activity ids.
     *
     * <p><i>Leaf instance</i> means this filter works for instances
     * of a user task is matched, but not the embedded sub process it is
     * contained in.
     */
    public function activityIdIn(array $activityIds): QueryInterface;

    /** Only selects process instances which are top level process instances. */
    public function rootProcessInstances(): QueryInterface;

    /** Only selects process instances which don't have subprocesses and thus are leaves of the execution tree. */
    public function leafProcessInstances(): QueryInterface;

    /** Only selects process instances which process definition has no tenant id. */
    public function processDefinitionWithoutTenantId(): QueryInterface;

    //ordering /////////////////////////////////////////////////////////////////

    /** Order by id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): QueryInterface;

    /** Order by process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): QueryInterface;

    /** Order by process definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): QueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of process instances without tenant id is database-specific.
     */
    public function orderByTenantId(): QueryInterface;

    /** Order by the business key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByBusinessKey(): QueryInterface;

    /**
     * <p>After calling or(), a chain of several filter criteria could follow. Each filter criterion that follows or()
     * will be linked together with an OR expression until the OR query is terminated. To terminate the OR query right
     * after the last filter criterion was applied, {@link #endOr()} must be invoked.</p>
     *
     * @return and object of the type ProcessInstanceQuery on which an arbitrary amount of filter criteria could be applied.
     * The several filter criteria will be linked together by an OR expression.
     *
     * @throws ProcessEngineException when or() has been invoked directly after or() or after or() and trailing filter
     * criteria. To prevent throwing this exception, {@link #endOr()} must be invoked after a chain of filter criteria to
     * mark the end of the OR query.
     * */
    public function or(): QueryInterface;

    /**
     * <p>endOr() terminates an OR query on which an arbitrary amount of filter criteria were applied. To terminate the
     * OR query which has been started by invoking {@link #or()}, endOr() must be invoked. Filter criteria which are
     * applied after calling endOr() are linked together by an AND expression.</p>
     *
     * @return and object of the type ProcessInstanceQuery on which an arbitrary amount of filter criteria could be applied.
     * The filter criteria will be linked together by an AND expression.
     *
     * @throws ProcessEngineException when endOr() has been invoked before {@link #or()} was invoked. To prevent throwing
     * this exception, {@link #or()} must be invoked first.
     * */
    public function endOr(): QueryInterface;
}
