<?php

namespace Jabe\Repository;

interface UpdateProcessDefinitionSuspensionStateSelectBuilderInterface
{
    /**
     * Selects the process definition with the given id.
     *
     * @param processDefinitionId
     *          id of the process definition
     * @return UpdateProcessDefinitionSuspensionStateBuilderInterface the builder
     */
    public function byProcessDefinitionId(?string $processDefinitionId): UpdateProcessDefinitionSuspensionStateBuilderInterface;

    /**
     * Selects the process definitions with the given key.
     *
     * @param processDefinitionKey
     *          key of the process definition
     * @return UpdateProcessDefinitionSuspensionStateTenantBuilderInterface the builder
     */
    public function byProcessDefinitionKey(?string $processDefinitionKey): UpdateProcessDefinitionSuspensionStateTenantBuilderInterface;
}
