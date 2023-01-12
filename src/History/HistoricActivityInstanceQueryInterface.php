<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricActivityInstanceQueryInterface extends QueryInterface
{
    /** Only select historic activity instances with the given id (primary key within history tables). */
    public function activityInstanceId(?string $activityInstanceId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances with the given process instance.
     * {@link ProcessInstance ) ids and HistoricProcessInstance ids match. */
    public function processInstanceId(?string $processInstanceId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for the given process definition */
    public function processDefinitionId(?string $processDefinitionId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for the given execution */
    public function executionId(?string $executionId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for the given activity (id from BPMN 2.0 XML) */
    public function activityId(?string $activityId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for activities with the given name */
    public function activityName(?string $activityName): HistoricActivityInstanceQueryInterface;

    /**
     * Only select historic activity instances for activities which activityName is like the given value.
     *
     * @param activityNameLike The string can include the wildcard character '%' to express
     *    like-strategy: starts with (string%), ends with (%string) or contains (%string%).
     */
    public function activityNameLike(?string $activityNameLike): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for activities with the given activity type */
    public function activityType(?string $activityType): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances for userTask activities assigned to the given user */
    public function taskAssignee(?string $userId): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that are finished. */
    public function finished(): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that are not finished yet. */
    public function unfinished(): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that complete a BPMN scope */
    public function completeScope(): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that got canceled */
    public function canceled(): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that were started before the given date. */
    public function startedBefore(?string $date): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that were started after the given date. */
    public function startedAfter(?string $date): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that were started before the given date. */
    public function finishedBefore(?string $date): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances that were started after the given date. */
    public function finishedAfter(?string $date): HistoricActivityInstanceQueryInterface;

    // ordering /////////////////////////////////////////////////////////////////
    /** Order by id (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricActivityInstanceId(): HistoricActivityInstanceQueryInterface;

    /** Order by processInstanceId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessInstanceId(): HistoricActivityInstanceQueryInterface;

    /** Order by executionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByExecutionId(): HistoricActivityInstanceQueryInterface;

    /** Order by activityId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityId(): HistoricActivityInstanceQueryInterface;

    /** Order by activityName (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityName(): HistoricActivityInstanceQueryInterface;

    /** Order by activityType (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByActivityType(): HistoricActivityInstanceQueryInterface;

    /** Order by start (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricActivityInstanceStartTime(): HistoricActivityInstanceQueryInterface;

    /** Order by end (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricActivityInstanceEndTime(): HistoricActivityInstanceQueryInterface;

    /** Order by duration (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByHistoricActivityInstanceDuration(): HistoricActivityInstanceQueryInterface;

    /** Order by processDefinitionId (needs to be followed by {@link #asc()} or {@link #desc()}). */
    public function orderByProcessDefinitionId(): HistoricActivityInstanceQueryInterface;

    /**
     * <p>Sort the {@link HistoricActivityInstance activity instances} in the order in which
     * they occurred (ie. started) and needs to be followed by {@link #asc()} or {@link #desc()}.</p>
     *
     * <p>The set of all {@link HistoricActivityInstance activity instances} is a <strong>partially
     * ordered set</strong>. At a BPMN level this means that instances of concurrent activities (example:
     * activities on different parallel branched after a parallel gateway) cannot be compared to each other.
     * Instances of activities which are part of happens-before relation at the BPMN level will be ordered
     * in respect to that relation.</p>
     *
     * <p>Technically this means that {@link HistoricActivityInstance activity instances}
     * with different {@link HistoricActivityInstance#getExecutionId() execution ids} are
     * <strong>incomparable</strong>. Only {@link HistoricActivityInstance activity instances} with
     * the same {@link HistoricActivityInstance#getExecutionId() execution id} can be <strong>totally
     * ordered</strong> by using {@link #executionId(String)} and {@link #orderPartiallyByOccurrence()}
     * which will return a result set ordered by its occurrence.</p>
     */
    public function orderPartiallyByOccurrence(): HistoricActivityInstanceQueryInterface;

    /** Only select historic activity instances with one of the given tenant ids. */
    public function tenantIdIn(array $tenantIds): HistoricActivityInstanceQueryInterface;

    /** Only selects historic activity instances that have no tenant id. */
    public function withoutTenantId(): HistoricActivityInstanceQueryInterface;

    /**
     * Order by tenant id (needs to be followed by {@link #asc()} or {@link #desc()}).
     * Note that the ordering of historic activity instances without tenant id is database-specific.
     */
    public function orderByTenantId(): HistoricActivityInstanceQueryInterface;
}
