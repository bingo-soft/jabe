<?php

namespace Jabe\Impl\Persistence\Deploy\Cache;

use Jabe\Impl\Context\Context;
use Jabe\Impl\Persistence\AbstractResourceDefinitionManagerInterface;
use Jabe\Impl\Persistence\Entity\FormDefinitionEntity;
use Jabe\Impl\Util\EnsureUtil;

class FormDefinitionCache extends ResourceDefinitionCache
{
    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity, CacheDeployer $cacheDeployer)
    {
        parent::__construct($factory, $cacheCapacity, $cacheDeployer);
    }

    protected function getManager(): AbstractResourceDefinitionManagerInterface
    {
        return Context::getCommandContext()->getFormDefinitionManager();
    }

    protected function checkInvalidDefinitionId(?string $definitionId): void
    {
        EnsureUtil::ensureNotNull("Invalid form definition id", "formDefinitionId", $definitionId);
    }

    protected function checkDefinitionFound(?string $definitionId, /*FormDefinitionEntity*/$definition): void
    {
        EnsureUtil::ensureNotNull("no deployed form definition found with id '" . $definitionId . "'", "formDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKey(?string $definitionKey, /*FormDefinitionEntity*/$definition): void
    {
        EnsureUtil::ensureNotNull("no deployed form definition found with key '" . $definitionKey . "'", "formDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyAndTenantId(?string $definitionKey, ?string $tenantId, /*FormDefinitionEntity*/$definition): void
    {
        EnsureUtil::ensureNotNull("no deployed form definition found with key '" . $definitionKey . "' and tenant-id '" . $tenantId . "'", "formDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyVersionAndTenantId(
        ?string $definitionKey,
        int $definitionVersion,
        ?string $tenantId,
        /*FormDefinitionEntity*/$definition
    ): void {
        EnsureUtil::ensureNotNull("no deployed form definition found with key '" . $definitionKey . "', version '" . $definitionVersion
          . "' and tenant-id '" . $tenantId . "'", "formDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyVersionTagAndTenantId(
        ?string $definitionKey,
        ?string $definitionVersionTag,
        ?string $tenantId,
        /*FormDefinitionEntity*/$definition
    ): void {
      // version tag is currently not supported for FormDefinition
    }

    protected function checkInvalidDefinitionByDeploymentAndKey(?string $deploymentId, ?string $definitionKey, /*FormDefinitionEntity*/$definition): void
    {
        EnsureUtil::ensureNotNull("no deployed form definition found with key '" . $definitionKey . "' in deployment '" . $deploymentId . "'", "formDefinition", $definition);
    }

    protected function checkInvalidDefinitionWasCached(?string $deploymentId, ?string $definitionId, /*FormDefinitionEntity*/$definition): void
    {
        EnsureUtil::ensureNotNull("deployment '" . $deploymentId . "' didn't put form definition '" . $definitionId . "' in the cache", "cachedProcessDefinition", $definition);
    }
}
