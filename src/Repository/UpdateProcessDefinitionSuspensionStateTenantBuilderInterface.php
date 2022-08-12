<?php

namespace Jabe\Repository;

interface UpdateProcessDefinitionSuspensionStateTenantBuilderInterface
{
    /**
     * Specify that the process definition belongs to no tenant.
     *
     * @return UpdateProcessDefinitionSuspensionStateBuilderInterface the builder
     */
    public function processDefinitionWithoutTenantId(): UpdateProcessDefinitionSuspensionStateBuilderInterface;

    /**
     * Specify the id of the tenant the process definition belongs to.
     *
     * @param tenantId
     *          the id of the tenant
     * @return UpdateProcessDefinitionSuspensionStateBuilderInterface the builder
     */
    public function processDefinitionTenantId(string $tenantId): UpdateProcessDefinitionSuspensionStateBuilderInterface;
}
