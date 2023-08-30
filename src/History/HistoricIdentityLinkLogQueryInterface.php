<?php

namespace Jabe\History;

use Jabe\Query\QueryInterface;

interface HistoricIdentityLinkLogQueryInterface extends QueryInterface
{
    /**
     * Only select historic identity links which have the date before the give date.
     **/
    public function dateBefore(?string $dateBefore): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the date after the give date.
     **/
    public function dateAfter(?string $dateAfter): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given identity link type.
     **/
    public function type(?string $type): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given user id.
     **/
    public function userId(?string $userId): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given group id.
     **/
    public function groupId(?string $groupId): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given task id.
     **/
    public function taskId(?string $taskId): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given process definition id.
     **/
    public function processDefinitionId(?string $processDefinitionId): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given process definition key.
     **/
    public function processDefinitionKey(?string $processDefinitionKey): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given operation type (add/delete).
     **/
    public function operationType(?string $operationType): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given assigner id.
     **/
    public function assignerId(?string $assignerId): HistoricIdentityLinkLogQueryInterface;

    /**
     * Only select historic identity links which have the given tenant id.
     **/
    public function tenantIdIn(array $tenantId): HistoricIdentityLinkLogQueryInterface;

    /** Only selects historic job log entries that have no tenant id. */
    public function withoutTenantId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by time (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByTime(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by type (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByType(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by userId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByUserId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by groupId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByGroupId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by taskId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByTaskId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by processDefinitionId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByProcessDefinitionId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by processDefinitionKey (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByProcessDefinitionKey(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by operationType (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByOperationType(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by assignerId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByAssignerId(): HistoricIdentityLinkLogQueryInterface;

    /**
     * Order by tenantId (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByTenantId(): HistoricIdentityLinkLogQueryInterface;
}
