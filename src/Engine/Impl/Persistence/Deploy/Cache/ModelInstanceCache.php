<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\GetDeploymentResourceCmd;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\EnginePersistenceLogger;
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Commons\Utils\Cache\CacheInterface;

abstract class ModelInstanceCache
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $instanceCache;
    protected $definitionCache;

    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity, ResourceDefinitionCache $definitionCache)
    {
        $this->instanceCache = $factory->createCache($cacheCapacity);
        $this->definitionCache = $definitionCache;
    }

    public function findBpmnModelInstanceForDefinition($definitionEl): ?ModelInstanceInterface
    {
        if ($definitionEl instanceof ResourceDefinitionEntityInterface) {
            $bpmnModelInstance = $this->instanceCache->get($definitionEl->getId());
            if ($bpmnModelInstance === null) {
                $bpmnModelInstance = $this->loadAndCacheBpmnModelInstance($definitionEl);
            }
            return $bpmnModelInstance;
        } elseif (is_string($definitionEl)) {
            $bpmnModelInstance = $this->instanceCache->get($definitionEl);
            if ($bpmnModelInstance === null) {
                $definition = $this->definitionCache->findDeployedDefinitionById($definitionEl);
                $bpmnModelInstance = $this->loadAndCacheBpmnModelInstance($definition);
            }
            return $bpmnModelInstance;
        }
    }

    protected function loadAndCacheBpmnModelInstance(ResourceDefinitionEntityInterface $definitionEntity): ?ModelInstanceInterface
    {
        $commandContext = Context::getCommandContext();
        $bpmnResourceInputStream = $commandContext->runWithoutAuthorization(
            function () use ($definitionEntity, $commandContext) {
                $cmd = new GetDeploymentResourceCmd($definitionEntity->getDeploymentId(), $definitionEntity->getResourceName());
                return $cmd->execute($commandContext);
            }
        );

        try {
            $bpmnModelInstance = $this->readModelFromStream($bpmnResourceInputStream);
            $this->instanceCache->put($definitionEntity->getId(), $bpmnModelInstance);
            return $bpmnModelInstance;
        } catch (\Exception $e) {
            $this->throwLoadModelException($definitionEntity->getId(), $e);
        }
        return null;
    }

    public function removeAllDefinitionsByDeploymentId(string $deploymentId): void
    {
        // remove all definitions for a specific deployment
        $allDefinitionsForDeployment = $this->getAllDefinitionsForDeployment($deploymentId);
        foreach ($allDefinitionsForDeployment as $definition) {
            try {
                $this->instanceCache->remove($definition->getId());
                $this->definitionCache->removeDefinitionFromCache($definition->getId());
            } catch (\Exception $e) {
                $this->logRemoveEntryFromDeploymentCacheFailure($definition->getId(), $e);
            }
        }
    }

    public function remove(string $definitionId): void
    {
        $this->instanceCache->remove($definitionId);
    }

    public function clear(): void
    {
        $this->instanceCache->clear();
    }

    public function getCache(): CacheInterface
    {
        return $this->instanceCache;
    }

    abstract protected function throwLoadModelException(string $definitionId, \Exception $e): void;

    abstract protected function logRemoveEntryFromDeploymentCacheFailure(string $definitionId, \Exception $e): void;

    abstract protected function readModelFromStream($stream): ModelInstanceInterface;

    abstract protected function getAllDefinitionsForDeployment(string $deploymentId): array;
}
