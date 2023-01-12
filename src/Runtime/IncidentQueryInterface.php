<?php

namespace Jabe\Runtime;

use Jabe\Query\QueryInterface;

interface IncidentQueryInterface extends QueryInterface
{
    /** Only select incidents which have the given id. **/
    public function incidentId(?string $incidentId): IncidentQueryInterface;

    /** Only select incidents which have the given incident type. **/
    public function incidentType(?string $incidentType): IncidentQueryInterface;

    /** Only select incidents which have the given incident message. **/
    public function incidentMessage(?string $incidentMessage): IncidentQueryInterface;

    /**
     * Only select incidents which incident message is like the given value.
     *
     * @param incidentMessageLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function incidentMessageLike(?string $incidentMessageLike): IncidentQueryInterface;

    /** Only select incidents which have the given process definition id. **/
    public function processDefinitionId(?string $processDefinitionId): IncidentQueryInterface;

    /** Only select incidents which have one of the given process definition keys. **/
    public function processDefinitionKeyIn(array $processDefinitionKeys): IncidentQueryInterface;

    /** Only select incidents which have the given process instance id. **/
    public function processInstanceId(?string $processInstanceId): IncidentQueryInterface;

    /** Only select incidents with the given id. **/
    public function executionId(?string $executionId): IncidentQueryInterface;

    /** Only select incidents which have an incidentTimestamp date before the given date **/
    public function incidentTimestampBefore(?string $incidentTimestampBefore): IncidentQueryInterface;

    /** Only select incidents which have an incidentTimestamp date after the given date **/
    public function incidentTimestampAfter(?string $incidentTimestampAfter): IncidentQueryInterface;

    /** Only select incidents which contain an activity with the given id. **/
    public function activityId(?string $activityId): IncidentQueryInterface;

    /** Only select incidents which were created due to a failure at an activity with the given id. **/
    public function failedActivityId(?string $activityId): IncidentQueryInterface;

    /** Only select incidents which contain the id of the cause incident. **/
    public function causeIncidentId(?string $causeIncidentId): IncidentQueryInterface;

    /** Only select incidents which contain the id of the root cause incident. **/
    public function rootCauseIncidentId(?string $rootCauseIncidentId): IncidentQueryInterface;

    /** Only select incidents which contain the configuration. **/
    public function configuration(?string $configuration): IncidentQueryInterface;

    /** Only select incidents that belong to one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): IncidentQueryInterface;

    /** Only select incidents that belong to one of the given job definition ids. */
    public function jobDefinitionIdIn(array $jobDefinitionIds): IncidentQueryInterface;

    /** Order by id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentId(): IncidentQueryInterface;

    /** Order by incidentTimestamp (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentTimestamp(): IncidentQueryInterface;

    /** Order by incident message (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentMessage(): IncidentQueryInterface;

    /** Order by incidentType (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByIncidentType(): IncidentQueryInterface;

    /** Order by executionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): IncidentQueryInterface;

    /** Order by activityId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): IncidentQueryInterface;

    /** Order by processInstanceId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): IncidentQueryInterface;

    /** Order by processDefinitionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): IncidentQueryInterface;

    /** Order by causeIncidentId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByCauseIncidentId(): IncidentQueryInterface;

    /** Order by rootCauseIncidentId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByRootCauseIncidentId(): IncidentQueryInterface;

    /** Order by configuration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByConfiguration(): IncidentQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of incidents without tenant id is database-specific.
     */
    public function orderByTenantId(): IncidentQueryInterface;
}
