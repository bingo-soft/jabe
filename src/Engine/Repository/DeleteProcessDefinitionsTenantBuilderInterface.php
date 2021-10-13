<?php

namespace BpmPlatform\Engine\Repository;

interface DeleteProcessDefinitionsTenantBuilderInterface extends DeleteProcessDefinitionsBuilderInterface
{
    /**
     * Process definitions which belong to no tenant will be removed.
     *
     * @return the builder
     */
    public function withoutTenantId(): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Process definitions which belong to the given tenant id will be removed.
     *
     * @param tenantId id which identifies the tenant
     * @return the builder
     */
    public function withTenantId(string $tenantId): DeleteProcessDefinitionsBuilderInterface;
}
