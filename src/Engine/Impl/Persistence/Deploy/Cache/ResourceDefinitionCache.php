<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\AbstractResourceDefinitionManagerInterface;
use Jabe\Engine\Impl\Persistence\Entity\DeploymentEntity;
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use Jabe\Commons\Utils\Cache\CacheInterface;

abstract class ResourceDefinitionCache
{
    protected $cache;
    protected $cacheDeployer;

    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity, CacheDeployer $cacheDeployer)
    {
        $this->cache = $factory->createCache($cacheCapacity);
        $this->cacheDeployer = $cacheDeployer;
    }

    public function findDefinitionFromCache(string $definitionId): ?ResourceDefinitionEntityInterface
    {
        return $this->cache->get($definitionId);
    }

    public function findDeployedDefinitionById(string $definitionId): ?ResourceDefinitionEntityInterface
    {
        $this->checkInvalidDefinitionId($definitionId);
        $definition = $this->getManager()->getCachedResourceDefinitionEntity($definitionId);
        if ($definition === null) {
            $definition = $this->getManager()
                ->findLatestDefinitionById($definitionId);
        }

        $this->checkDefinitionFound($definitionId, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    /**
     * @return ResourceDefinitionEntityInterface the latest version of the definition with the given key (from any tenant)
     * @throws ProcessEngineException if more than one tenant has a definition with the given key
     */
    public function findDeployedLatestDefinitionByKey(string $definitionKey): ?ResourceDefinitionEntityInterface
    {
        $definition = $this->getManager()->findLatestDefinitionByKey($definitionKey);
        $this->checkInvalidDefinitionByKey($definitionKey, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    public function findDeployedLatestDefinitionByKeyAndTenantId(string $definitionKey, string $tenantId): ?ResourceDefinitionEntityInterface
    {
        $definition = $this->getManager()->findLatestDefinitionByKeyAndTenantId($definitionKey, $tenantId);
        $this->checkInvalidDefinitionByKeyAndTenantId($definitionKey, $tenantId, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    public function findDeployedDefinitionByKeyVersionAndTenantId(string $definitionKey, int $definitionVersion, string $tenantId): ?ResourceDefinitionEntityInterface
    {
        $commandContext = Context::getCommandContext();
        $scope = $this;
        $definition = $commandContext->runWithoutAuthorization(function () use ($scope, $definitionKey, $definitionVersion, $tenantId) {
            return $scope->getManager()->findDefinitionByKeyVersionAndTenantId($definitionKey, $definitionVersion, $tenantId);
        });
        $this->checkInvalidDefinitionByKeyVersionAndTenantId($definitionKey, $definitionVersion, $tenantId, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    public function findDeployedDefinitionByKeyVersionTagAndTenantId(string $definitionKey, string $definitionVersionTag, string $tenantId): ?ResourceDefinitionEntityInterface
    {
        $commandContext = Context::getCommandContext();
        $scope = $this;
        $definition = $commandContext->runWithoutAuthorization(function () use ($scope, $definitionVersionTag, $tenantId) {
            return $scope->getManager()->findDefinitionByKeyVersionTagAndTenantId($definitionKey, $definitionVersionTag, $tenantId);
        });
        $this->checkInvalidDefinitionByKeyVersionTagAndTenantId($definitionKey, $definitionVersionTag, $tenantId, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    public function findDeployedDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey): ?ResourceDefinitionEntityInterface
    {
        $definition = $this->getManager()->findDefinitionByDeploymentAndKey($deploymentId, $definitionKey);
        $this->checkInvalidDefinitionByDeploymentAndKey($deploymentId, $definitionKey, $definition);
        $definition = $this->resolveDefinition($definition);
        return $definition;
    }

    public function resolveDefinition(ResourceDefinitionEntityInterface $definition): ?ResourceDefinitionEntityInterface
    {
        $definitionId = $definition->getId();
        $deploymentId = $definition->getDeploymentId();
        $cachedDefinition = $this->cache->get($definitionId);
        if ($cachedDefinition === null) {
            $cachedDefinition = $this->cache->get($definitionId);
            if ($cachedDefinition === null) {
                $deployment = Context::getCommandContext()
                    ->getDeploymentManager()
                    ->findDeploymentById($deploymentId);
                $deployment->setNew(false);
                $this->cacheDeployer->deployOnlyGivenResourcesOfDeployment($deployment, $definition->getResourceName(), $definition->getDiagramResourceName());
                $cachedDefinition = $this->cache->get($definitionId);
            }
            $this->checkInvalidDefinitionWasCached($deploymentId, $definitionId, $cachedDefinition);
        }
        if ($cachedDefinition !== null) {
            $cachedDefinition->updateModifiableFieldsFromEntity($definition);
        }
        return $cachedDefinition;
    }

    public function addDefinition(ResourceDefinitionEntityInterface $definition): void
    {
        $this->cache->put($definition->getId(), $definition);
    }

    public function getDefinition(string $id): ?ResourceDefinitionEntityInterface
    {
        return $this->cache->get($id);
    }

    public function removeDefinitionFromCache(string $id): void
    {
        $this->cache->remove($id);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    abstract protected function getManager(): AbstractResourceDefinitionManagerInterface;

    abstract protected function checkInvalidDefinitionId(string $definitionId): void;

    abstract protected function checkDefinitionFound(string $definitionId, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionByKey(string $definitionKey, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionByKeyAndTenantId(string $definitionKey, string $tenantId, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionByKeyVersionAndTenantId(string $definitionKey, int $definitionVersion, string $tenantId, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionByKeyVersionTagAndTenantId(string $definitionKey, string $definitionVersionTag, string $tenantId, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey, ResourceDefinitionEntityInterface $definition): void;

    abstract protected function checkInvalidDefinitionWasCached(string $deploymentId, string $definitionId, ResourceDefinitionEntityInterface $definition): void;
}
