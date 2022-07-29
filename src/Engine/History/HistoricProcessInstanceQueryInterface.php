<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Query\QueryInterface;

interface HistoricProcessInstanceQueryInterface extends QueryInterface
{
    /** Only select historic process instances with the given process instance.
     * {@link ProcessInstance) ids and HistoricProcessInstance ids match. */
    public function processInstanceId(string $processInstanceId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances whose id is in the given set of ids.
     * {@link ProcessInstance) ids and HistoricProcessInstance ids match. */
    public function processInstanceIds(array $processInstanceIds): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances for the given process definition */
    public function processDefinitionId(string $processDefinitionId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are defined by a process
     * definition with the given key.  */
    public function processDefinitionKey(string $processDefinitionKey): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are defined by any given process
     * definition key.  */
    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that don't have a process-definition of which the key is present in the given list */
    public function processDefinitionKeyNotIn(array $processDefinitionKeys): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are defined by a process
     * definition with the given name.  */
    public function processDefinitionName(string $processDefinitionName): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances that are defined by process definition which name
     * is like the given value.
     *
     * @param nameLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function processDefinitionNameLike(string $nameLike): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances with the given business key */
    public function processInstanceBusinessKey(string $processInstanceBusinessKey): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances which had a business key like the given value.
     *
     * @param processInstanceBusinessKeyLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function processInstanceBusinessKeyLike(string $processInstanceBusinessKeyLike): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are completely finished. */
    public function finished(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instance that are not yet finished. */
    public function unfinished(): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances with incidents
     *
     * @return HistoricProcessInstanceQuery
     */
    public function withIncidents(): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances with root incidents
     *
     * @return HistoricProcessInstanceQuery
     */
    public function withRootIncidents(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances with incident status either 'open' or 'resolved'.
     * To get all process instances with incidents, use HistoricProcessInstanceQuery#withIncidents().
     *
     * @param status indicates the incident status, which is either 'open' or 'resolved'
     * @return HistoricProcessInstanceQuery
     */
    public function incidentStatus(string $status): HistoricProcessInstanceQueryInterface;

    /**
     * Only selects process instances with the given incident type.
     */
    public function incidentType(string $incidentType): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances with the given incident message.
     *
     * @param incidentMessage Incidents Message for which the historic process instances should be selected
     *
     * @return HistoricProcessInstanceQuery
     */
    public function incidentMessage(string $incidentMessage): HistoricProcessInstanceQueryInterface;

    /**
     * Only select historic process instances which had an incident message like the given value.
     *
     * @param incidentMessageLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     *
     * @return HistoricProcessInstanceQuery
     */
    public function incidentMessageLike(string $incidentMessageLike): HistoricProcessInstanceQueryInterface;

    /**
     * The query will match the names of variables in a case-insensitive way.
     */
    public function matchVariableNamesIgnoreCase(): HistoricProcessInstanceQueryInterface;

    /**
     * The query will match the values of variables in a case-insensitive way.
     */
    public function matchVariableValuesIgnoreCase(): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had a global variable with the given value
     * when they ended. Only select process instances which have a variable value
     * greater than the passed value. The type only applies to already ended
     * process instances, otherwise use a ProcessInstanceQuery instead! of
     * variable is determined based on the value, using types configured in
     * ProcessEngineConfiguration#getVariableSerializers(). Byte-arrays and
     * Serializable objects (which are not primitive type wrappers) are
     * not supported.
     * @param name of the variable, cannot be null. */
    public function variableValueEquals(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had a global variable with the given name, but
     * with a different value than the passed value when they ended. Only select
     * process instances which have a variable value greater than the passed
     * value. Byte-arrays and Serializable objects (which are not
     * primitive type wrappers) are not supported.
     * @param name of the variable, cannot be null. */
    public function variableValueNotEquals(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had a global variable value greater than the
     * passed value when they ended. Booleans, Byte-arrays and
     * Serializable objects (which are not primitive type wrappers) are
     * not supported. Only select process instances which have a variable value
     * greater than the passed value.
     * @param name cannot be null.
     * @param value cannot be null. */
    public function variableValueGreaterThan(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had a global variable value greater than or
     * equal to the passed value when they ended. Booleans, Byte-arrays and
     * Serializable objects (which are not primitive type wrappers) are
     * not supported. Only applies to already ended process instances, otherwise
     * use a ProcessInstanceQuery instead!
     * @param name cannot be null.
     * @param value cannot be null. */
    public function variableValueGreaterThanOrEqual(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had a global variable value less than the
     * passed value when the ended. Only applies to already ended process
     * instances, otherwise use a ProcessInstanceQuery instead! Booleans,
     * Byte-arrays and Serializable objects (which are not primitive type
     * wrappers) are not supported.
     * @param name cannot be null.
     * @param value cannot be null. */
    public function variableValueLessThan(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which has a global variable value less than or equal
     * to the passed value when they ended. Only applies to already ended process
     * instances, otherwise use a ProcessInstanceQuery instead! Booleans,
     * Byte-arrays and Serializable objects (which are not primitive type
     * wrappers) are not supported.
     * @param name cannot be null.
     * @param value cannot be null. */
    public function variableValueLessThanOrEqual(string $name, $value): HistoricProcessInstanceQueryInterface;

    /** Only select process instances which had global variable value like the given value
     * when they ended. Only applies to already ended process instances, otherwise
     * use a ProcessInstanceQuery instead! This can be used on string
     * variables only.
     * @param name cannot be null.
     * @param value cannot be null. The string can include the
     *          wildcard character '%' to express like-strategy: starts with
     *          (string%), ends with (%string) or contains (%string%). */
    public function variableValueLike(string $name, string $value): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that were started before the given date. */
    public function startedBefore(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that were started after the given date. */
    public function startedAfter(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that were started before the given date. */
    public function finishedBefore(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that were started after the given date. */
    public function finishedAfter(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instance that are started by the given user. */
    public function startedBy(string $userId): HistoricProcessInstanceQueryInterface;

    /** Order by the process instance id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricProcessInstanceQueryInterface;

    /** Order by the process definition id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricProcessInstanceQueryInterface;

    /** Order by the process definition key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): HistoricProcessInstanceQueryInterface;

    /** Order by the process definition name (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionName(): HistoricProcessInstanceQueryInterface;

    /** Order by the process definition version (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionVersion(): HistoricProcessInstanceQueryInterface;

    /** Order by the business key (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceBusinessKey(): HistoricProcessInstanceQueryInterface;

    /** Order by the start time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceStartTime(): HistoricProcessInstanceQueryInterface;

    /** Order by the end time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceEndTime(): HistoricProcessInstanceQueryInterface;

    /** Order by the duration of the process instance (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceDuration(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are top level process instances. */
    public function rootProcessInstances(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances started by the given process
     * instance. {@link ProcessInstance) ids and HistoricProcessInstance
     * ids match. */
    public function superProcessInstanceId(string $superProcessInstanceId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances having a sub process instance
     * with the given process instance id.
     *
     * Note that there will always be maximum only <b>one</b>
     * such process instance that can be the result of this query.
     */
    public function subProcessInstanceId(string $subProcessInstanceId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances started by the given case
     * instance. */
    //public function superCaseInstanceId(string $superCaseInstanceId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances having a sub case instance
     * with the given case instance id.
     *
     * Note that there will always be maximum only <b>one</b>
     * such process instance that can be the result of this query.
     */
    //public function subCaseInstanceId(string $subCaseInstanceId): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricProcessInstanceQueryInterface;

    /** Only selects historic process instances which have no tenant id. */
    public function withoutTenantId(): HistoricProcessInstanceQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of historic process instances without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that executed an activity after the given date. */
    public function executedActivityAfter(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that executed an activity before the given date. */
    public function executedActivityBefore(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that executed activities with given ids. */
    public function executedActivityIdIn(array $ids): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that have active activities with given ids. */
    public function activeActivityIdIn(array $ids): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that executed an job after the given date. */
    public function executedJobAfter(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that executed an job before the given date. */
    public function executedJobBefore(string $date): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are active. */
    public function active(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are suspended. */
    public function suspended(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are completed. */
    public function completed(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are externallyTerminated. */
    public function externallyTerminated(): HistoricProcessInstanceQueryInterface;

    /** Only select historic process instances that are internallyTerminated. */
    public function internallyTerminated(): HistoricProcessInstanceQueryInterface;

    /**
     * <p>After calling or(), a chain of several filter criteria could follow. Each filter criterion that follows or()
     * will be linked together with an OR expression until the OR query is terminated. To terminate the OR query right
     * after the last filter criterion was applied, {@link #endOr()} must be invoked.</p>
     *
     * @return an object of the type HistoricProcessInstanceQuery on which an arbitrary amount of filter criteria could be applied.
     * The several filter criteria will be linked together by an OR expression.
     *
     * @throws ProcessEngineException when or() has been invoked directly after or() or after or() and trailing filter
     * criteria. To prevent throwing this exception, {@link #endOr()} must be invoked after a chain of filter criteria to
     * mark the end of the OR query.
     * */
    public function or(): HistoricProcessInstanceQueryInterface;

    /**
     * <p>endOr() terminates an OR query on which an arbitrary amount of filter criteria were applied. To terminate the
     * OR query which has been started by invoking {@link #or()}, endOr() must be invoked. Filter criteria which are
     * applied after calling endOr() are linked together by an AND expression.</p>
     *
     * @return an object of the type HistoricProcessInstanceQuery on which an arbitrary amount of filter criteria could be applied.
     * The filter criteria will be linked together by an AND expression.
     *
     * @throws ProcessEngineException when endOr() has been invoked before {@link #or()} was invoked. To prevent throwing
     * this exception, {@link #or()} must be invoked first.
     * */
    public function endOr(): HistoricProcessInstanceQueryInterface;
}
