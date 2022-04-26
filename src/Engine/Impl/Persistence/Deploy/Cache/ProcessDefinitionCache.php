<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\AbstractResourceDefinitionManagerInterface;
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ProcessDefinitionCache extends ResourceDefinitionCache
{
    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity, CacheDeployer $cacheDeployer)
    {
        parent::__construct($factory, $cacheCapacity, $cacheDeployer);
    }

    protected function getManager(): AbstractResourceDefinitionManagerInterface
    {
        return Context::getCommandContext()->getProcessDefinitionManager();
    }

    protected function checkInvalidDefinitionId(string $definitionId): void
    {
        EnsureUtil::ensureNotNull("Invalid process definition id", "processDefinitionId", $definitionId);
    }

    protected function checkDefinitionFound(string $definitionId, ProcessDefinitionEntity $definition): void
    {
        EnsureUtil::ensureNotNull("no deployed process definition found with id '" . $definitionId . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKey(string $definitionKey, ProcessDefinitionEntity $definition): void
    {
        EnsureUtil::ensureNotNull("no processes deployed with key '" . $definitionKey . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyAndTenantId(string $definitionKey, string $tenantId, ProcessDefinitionEntity $definition): void
    {
        EnsureUtil::ensureNotNull("no processes deployed with key '" . $definitionKey . "' and tenant-id '" . $tenantId . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyVersionAndTenantId(
        string $definitionKey,
        int $definitionVersion,
        string $tenantId,
        ProcessDefinitionEntity $definition
    ): void {
        EnsureUtil::ensureNotNull("no processes deployed with key = '" . $definitionKey . "', version = '" . $definitionVersion
          . "' and tenant-id = '" . $tenantId . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionByKeyVersionTagAndTenantId(
        string $definitionKey,
        string $definitionVersionTag,
        string $tenantId,
        ProcessDefinitionEntity $definition
    ): void {
        EnsureUtil::ensureNotNull("no processes deployed with key = '" . $definitionKey . "', versionTag = '" . $definitionVersionTag
          . "' and tenant-id = '" . $tenantId . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey, ProcessDefinitionEntity $definition): void
    {
        EnsureUtil::ensureNotNull("no processes deployed with key = '" . $definitionKey . "' in deployment = '" . $deploymentId . "'", "processDefinition", $definition);
    }

    protected function checkInvalidDefinitionWasCached(string $deploymentId, string $definitionId, ProcessDefinitionEntity $definition): void
    {
        EnsureUtil::ensureNotNull("deployment '" . $deploymentId . "' didn't put process definition '" . $definitionId . "' in the cache", "cachedProcessDefinition", $definition);
    }
}
