<?php

namespace BpmPlatform\Engine\Runtime;

use BpmPlatform\Engine\History\HistoricProcessInstanceQueryInterface;

interface UpdateProcessInstancesRequestInterface
{
    /**
     * Selects a list of process instances with the given list of ids.
     *
     * @param processInstanceIds
     *          list of ids of the process instances
     * @return the builder
     */
    public function byProcessInstanceIds(array $processInstanceIds): UpdateProcessInstancesSuspensionStateBuilderInterface;

    /**
     * Selects a list of process instances with the given a process instance query.
     *
     * @param processInstanceQuery
     *          process instance query that discribes a list of the process instances
     * @return the builder
     */
    public function byProcessInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): UpdateProcessInstancesSuspensionStateBuilder;

    /**
     * Selects a list of process instances with the given a historical process instance query.
     *
     * @param historicProcessInstanceQuery
     *          historical process instance query that discribes a list of the process instances
     * @return the builder
     */
    public function byHistoricProcessInstanceQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): UpdateProcessInstancesSuspensionStateBuilderInterface;
}
