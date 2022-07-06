<?php

namespace Jabe\Engine\Repository;

interface DeleteProcessDefinitionsTenantBuilderInterface extends DeleteProcessDefinitionsBuilderInterface
{
    /**
     * Process definitions which belong to no tenant will be removed.
     *
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function withoutTenantId(): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Process definitions which belong to the given tenant id will be removed.
     *
     * @param tenantId id which identifies the tenant
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function withTenantId(string $tenantId): DeleteProcessDefinitionsBuilderInterface;
}
