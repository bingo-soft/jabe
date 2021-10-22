<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Query\QueryInterface;

interface CleanableHistoricProcessInstanceReportInterface extends QueryInterface
{
    /**
     * Only takes historic process instances into account for the given process definition ids.
     *
     * @throws NotValidException if one of the given ids is null
     */
    public function processDefinitionIdIn(array $processDefinitionIds): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Only takes historic process instances into account for the given process definition keys.
     *
     * @throws NotValidException if one of the given keys is null
     */
    public function processDefinitionKeyIn(array $processDefinitionKeys): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Only select historic process instances with one of the given tenant ids.
     *
     * @throws NotValidException if one of the given ids is null
     */
    public function tenantIdIn(array $tenantIds): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Only selects historic process instances which have no tenant id.
     */
    public function withoutTenantId(): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Only selects historic process instances which have more than zero finished instances.
     */
    public function compact(): CleanableHistoricProcessInstanceReportInterface;

    /**
     * Order by finished process instances amount (needs to be followed by {@link #asc()} or {@link #desc()}).
     */
    public function orderByFinished(): CleanableHistoricProcessInstanceReportInterface;
}
