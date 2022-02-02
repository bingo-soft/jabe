<?php

namespace BpmPlatform\Engine\Impl\Persistence;

interface AbstractResourceDefinitionManagerInterface
{
    public function findLatestDefinitionByKey(string $key);

    public function findLatestDefinitionById(string $id);

    public function findLatestDefinitionByKeyAndTenantId(string $definitionKey, string $tenantId);

    public function findDefinitionByKeyVersionAndTenantId(string $definitionKey, int $definitionVersion, string $tenantId);

    public function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey);

    public function getCachedResourceDefinitionEntity(string $definitionId);

    public function findDefinitionByKeyVersionTagAndTenantId(string $definitionKey, string $definitionVersionTag, string $tenantId);
}
