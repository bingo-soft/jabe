<?php

namespace Jabe\Engine\Management;

interface UpdateJobDefinitionSuspensionStateSelectBuilderInterface
{
    /**
     * Selects the job definition with the given id.
     *
     * @param jobDefinitionId
     *          id of the job definition
     * @return UpdateJobDefinitionSuspensionStateBuilderInterface the builder
     */
    public function byJobDefinitionId(string $jobDefinitionId): UpdateJobDefinitionSuspensionStateBuilderInterface;

    /**
     * Selects the job definitions of the process definition with the given id.
     *
     * @param processDefinitionId
     *          id of the process definition
     * @return UpdateJobDefinitionSuspensionStateBuilderInterface the builder
     */
    public function byProcessDefinitionId(string $processDefinitionId): UpdateJobDefinitionSuspensionStateBuilderInterface;

    /**
     * Selects the job definitions of the process definitions with the given key.
     *
     * @param processDefinitionKey
     *          key of the process definition
     * @return UpdateJobDefinitionSuspensionStateTenantBuilderInterface the builder
     */
    public function byProcessDefinitionKey(string $processDefinitionKey): UpdateJobDefinitionSuspensionStateTenantBuilderInterface;
}
