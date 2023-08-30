<?php

namespace Jabe\History;

use Jabe\Query\ReportInterface;

interface HistoricProcessInstanceReportInterface extends ReportInterface
{
    /**
     * Only takes historic process instances into account that were started before the given date.
     *
     * @throws NotValidException if the given started before date is null
     *
     */
    public function startedBefore(?string $startedBefore): HistoricProcessInstanceReportInterface;

    /**
     * Only takes historic process instances into account that were started after the given date.
     *
     * @throws NotValidException if the given started after date is null
     */
    public function startedAfter(?string $startedAfter): HistoricProcessInstanceReportInterface;

    /**
     * Only takes historic process instances into account for the given process definition ids.
     *
     * @throws NotValidException if one of the given ids is null
     */
    public function processDefinitionIdIn(array $processDefinitionIds): HistoricProcessInstanceReportInterface;

    /**
     * Only takes historic process instances into account for the given process definition keys.
     *
     * @throws NotValidException if one of the given ids is null
     */
    public function processDefinitionKeyIn(array $processDefinitionKeys): HistoricProcessInstanceReportInterface;
}
