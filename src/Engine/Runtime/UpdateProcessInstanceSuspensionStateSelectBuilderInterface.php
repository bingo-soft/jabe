<?php

namespace BpmPlatform\Engine\Runtime;

interface UpdateProcessInstanceSuspensionStateSelectBuilderInterface extends UpdateProcessInstancesRequestInterface
{
    /**
     * Selects the process instance with the given id.
     *
     * @param processInstanceId
     *          id of the process instance
     * @return the builder
     */
    public function byProcessInstanceId(string $processInstanceId): UpdateProcessInstanceSuspensionStateBuilderInterface;

    /**
     * Selects the instances of the process definition with the given id.
     *
     * @param processDefinitionId
     *          id of the process definition
     * @return the builder
     */
    public function byProcessDefinitionId(string $processDefinitionId): UpdateProcessInstanceSuspensionStateBuilderInterface;

    /**
     * Selects the instances of the process definitions with the given key.
     *
     * @param processDefinitionKey
     *          key of the process definition
     * @return the builder
     */
    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateProcessInstanceSuspensionStateTenantBuilderInterface;
}
