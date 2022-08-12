<?php

namespace Jabe\History;

interface HistoricTaskInstanceReportResultInterface
{
    /**
     * <p>Returns the count of the grouped items.</p>
     */
    public function getCount(): int;

    /**
     * <p>Returns the process definition key for the selected definition key.</p>
     */
    public function getProcessDefinitionKey(): string;

    /**
     * <p>Returns the process definition id for the selected definition key</p>
     */
    public function getProcessDefinitionId(): string;

    /**
     * <p></p>Returns the process definition name for the selected definition key</p>
     */
    public function getProcessDefinitionName(): string;

    /**
     * <p>Returns the name of the task</p>
     *
     * @return A task name when the query is triggered with a 'countByTaskName'. Else the return
     * value is null.
     */
    public function getTaskName(): string;

    /**
     * <p>Returns the id of the tenant task</p>
     */
    public function getTenantId(): ?string;
}
