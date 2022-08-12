<?php

namespace Jabe\History;

interface CleanableHistoricProcessInstanceReportResultInterface
{
    /**
     * Returns the process definition id for the selected definition.
     */
    public function getProcessDefinitionId(): string;

    /**
     * Returns the process definition key for the selected definition.
     */
    public function getProcessDefinitionKey(): string;

    /**
     * Returns the process definition name for the selected definition.
     */
    public function getProcessDefinitionName(): string;

    /**
     * Returns the process definition version for the selected definition.
     */
    public function getProcessDefinitionVersion(): int;

    /**
     * Returns the history time to live for the selected definition.
     */
    public function getHistoryTimeToLive(): int;

    /**
     * Returns the amount of finished historic process instances.
     */
    public function getFinishedProcessInstanceCount(): int;

    /**
     * Returns the amount of cleanable historic process instances.
     */
    public function getCleanableProcessInstanceCount(): int;

    /**
     *
     * Returns the tenant id of the current process instances.
     */
    public function getTenantId(): ?string;
}
