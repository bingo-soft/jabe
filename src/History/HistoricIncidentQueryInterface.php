<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricIncidentQueryInterface extends QueryInterface
{
    /** Only select historic incidents which have the given id. **/
    public function incidentId(string $incidentId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have the given incident type. **/
    public function incidentType(string $incidentType): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have the given incident message. **/
    public function incidentMessage(string $incidentMessage): HistoricIncidentQueryInterface;

    /**
     * Only select historic incidents which incident message is like the given value
     *
     * @param incidentMessageLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function incidentMessageLike(string $incidentMessageLike): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have the given process definition id. **/
    public function processDefinitionId(string $processDefinitionId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have the given processDefinitionKey. **/
    public function processDefinitionKey(string $processDefinitionKey): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have one of the given process definition keys. **/
    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have the given process instance id. **/
    public function processInstanceId(string $processInstanceId): HistoricIncidentQueryInterface;

    /** Only select historic incidents with the given id. **/
    public function executionId(string $executionId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have a createTime date before the given date **/
    public function createTimeBefore(string $createTimeBefore): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have a createTime date after the given date **/
    public function createTimeAfter(string $createTimeAfter): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have an endTimeBefore date before the given date **/
    public function endTimeBefore(string $endTimeBefore): HistoricIncidentQueryInterface;

    /** Only select historic incidents which have an endTimeAfter date after the given date **/
    public function endTimeAfter(string $endTimeAfter): HistoricIncidentQueryInterface;

    /** Only select historic incidents which contain an activity with the given id. **/
    public function activityId(string $activityId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which were created due to a failure at an activity with the given id. **/
    public function failedActivityId(string $activityId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which contain the id of the cause incident. **/
    public function causeIncidentId(string $causeIncidentId): HistoricIncidentQueryInterface;

    /** Only select historic incidents which contain the id of the root cause incident. **/
    public function rootCauseIncidentId(string $rootCauseIncidentId): HistoricIncidentQueryInterface;

    /** Only select historic incidents that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricIncidentQueryInterface;

    /** Only selects historic incidents that have no tenant id. */
    public function withoutTenantId(): HistoricIncidentQueryInterface;

    /** Only select incidents which contain the configuration. **/
    public function configuration(string $configuration): HistoricIncidentQueryInterface;

    /** Only select incidents which contain the historyConfiguration. **/
    public function historyConfiguration(string $historyConfiguration): HistoricIncidentQueryInterface;

    /** Only select incidents that belong to one of the given job definition ids. */
    public function jobDefinitionIdIn(array $jobDefinitionIds): HistoricIncidentQueryInterface;

    /** Only select historic incidents which are open. **/
    public function open(): HistoricIncidentQueryInterface;

    /** Only select historic incidents which are resolved. **/
    public function resolved(): HistoricIncidentQueryInterface;

    /** Only select historic incidents which are deleted. **/
    public function deleted(): HistoricIncidentQueryInterface;

    /** Order by id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentId(): HistoricIncidentQueryInterface;

    /** Order by message (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentMessage(): HistoricIncidentQueryInterface;

    /** Order by create time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCreateTime(): HistoricIncidentQueryInterface;

    /** Order by end time (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByEndTime(): HistoricIncidentQueryInterface;

    /** Order by incidentType (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentType(): HistoricIncidentQueryInterface;

    /** Order by executionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): HistoricIncidentQueryInterface;

    /** Order by activityId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): HistoricIncidentQueryInterface;

    /** Order by processInstanceId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricIncidentQueryInterface;

    /** Order by processDefinitionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricIncidentQueryInterface;

    /** Order by processDefinitionKey (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionKey(): HistoricIncidentQueryInterface;

    /** Order by causeIncidentId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCauseIncidentId(): HistoricIncidentQueryInterface;

    /** Order by rootCauseIncidentId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByRootCauseIncidentId(): HistoricIncidentQueryInterface;

    /** Order by configuration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByConfiguration(): HistoricIncidentQueryInterface;

    /** Order by historyConfiguration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoryConfiguration(): HistoricIncidentQueryInterface;

    /** Order by incidentState (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentState(): HistoricIncidentQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of incidents without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricIncidentQueryInterface;
}
