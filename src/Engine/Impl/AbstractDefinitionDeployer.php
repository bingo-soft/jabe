<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Cfg\{
    IdGeneratorInterface,
    ProcessEngineConfigurationImpl
};
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Core\Model\Properties;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Deploy\DeployerInterface;
use Jabe\Engine\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ResourceEntity
};
use Jabe\Engine\Impl\Repository\ResourceDefinitionEntityInterface;

abstract class AbstractDefinitionDeployer implements DeployerInterface
{
    public const DIAGRAM_SUFFIXES = [ "png", "jpg", "gif", "svg" ];

    //private final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $idGenerator;

    public function getIdGenerator(): IdGeneratorInterface
    {
        return $this->idGenerator;
    }

    public function setIdGenerator(IdGeneratorInterface $idGenerator): void
    {
        $this->idGenerator = $idGenerator;
    }

    public function deploy(DeploymentEntity $deployment): void
    {
        //LOG.debugProcessingDeployment(deployment.getName());
        $properties = new Properties();
        $definitions = $this->parseDefinitionResources($deployment, $properties);
        $this->ensureNoDuplicateDefinitionKeys($definitions);
        $this->postProcessDefinitions($deployment, $definitions, $properties);
    }

    protected function parseDefinitionResources(DeploymentEntity $deployment, Properties $properties): array
    {
        $definitions = [];
        foreach ($deployment->getResources() as $resource) {
            //LOG.debugProcessingResource(resource.getName());
            if ($this->isResourceHandled($resource)) {
                $definitions = array_merge($definitions, $this->transformResource($deployment, $resource, $properties));
            }
        }
        return $definitions;
    }

    protected function isResourceHandled(ResourceEntity $resource): bool
    {
        $resourceName = $resource->getName();

        foreach ($this->getResourcesSuffixes() as $suffix) {
            if (str_ends_with($resourceName, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array the list of resource suffixes for this cacheDeployer
     */
    abstract protected function getResourcesSuffixes(): array;

    protected function transformResource(DeploymentEntity $deployment, ResourceEntity $resource, Properties $properties): array
    {
        $resourceName = $resource->getName();
        $definitions = $this->transformDefinitions($deployment, $resource, $properties);

        foreach ($definitions as $definition) {
            $definition->setResourceName($resourceName);

            $diagramResourceName = $this->getDiagramResourceForDefinition($deployment, $resourceName, $definition, $deployment->getResources());
            if ($diagramResourceName !== null) {
                $definition->setDiagramResourceName($diagramResourceName);
            }
        }

        return $definitions;
    }

    /**
     * Transform the resource entity into definition entities.
     *
     * @param deployment the deployment the resources belongs to
     * @param resource the resource to transform
     * @return a list of transformed definition entities
     */
    abstract protected function transformDefinitions(DeploymentEntity $deployment, ResourceEntity $resource, Properties $properties): array;

    /**
     * Returns the default name of the image resource for a certain definition.
     *
     * It will first look for an image resource which matches the definition
     * specifically, before resorting to an image resource which matches the file
     * containing the definition.
     *
     * Example: if the deployment contains a BPMN 2.0 xml resource called
     * 'abc.bpmn20.xml' containing only one process with key 'myProcess', then
     * this method will look for an image resources called 'abc.myProcess.png'
     * (or .jpg, or .gif, etc.) or 'abc.png' if the previous one wasn't found.
     *
     * Example 2: if the deployment contains a BPMN 2.0 xml resource called
     * 'abc.bpmn20.xml' containing three processes (with keys a, b and c),
     * then this method will first look for an image resource called 'abc.a.png'
     * before looking for 'abc.png' (likewise for b and c).
     * Note that if abc.a.png, abc.b.png and abc.c.png don't exist, all
     * processes will have the same image: abc.png.
     *
     * @return string null if no matching image resource is found.
     */
    protected function getDiagramResourceForDefinition(DeploymentEntity $deployment, string $resourceName, DefinitionEntity $definition, array $resources): ?string
    {
        foreach ($this->getDiagramSuffixes() as $diagramSuffix) {
            $definitionDiagramResource = $this->getDefinitionDiagramResourceName($resourceName, $definition, $diagramSuffix);
            $diagramForFileResource = $this->getGeneralDiagramResourceName($resourceName, $definition, $diagramSuffix);
            if (array_key_exists($definitionDiagramResource, $resources)) {
                return $definitionDiagramResource;
            } elseif (array_key_exists($diagramForFileResource, $resources)) {
                return $diagramForFileResource;
            }
        }
        // no matching diagram found
        return null;
    }

    protected function getDefinitionDiagramResourceName(string $resourceName, DefinitionEntity $definition, string $diagramSuffix): string
    {
        $fileResourceBase = $this->stripDefinitionFileSuffix($resourceName);
        $definitionKey = $definition->getKey();
        return $fileResourceBase . $definitionKey . "." . $diagramSuffix;
    }

    protected function getGeneralDiagramResourceName(string $resourceName, DefinitionEntity $definition, string $diagramSuffix): string
    {
        $fileResourceBase = $this->stripDefinitionFileSuffix($resourceName);

        return $fileResourceBase . $diagramSuffix;
    }

    protected function stripDefinitionFileSuffix(string $resourceName): string
    {
        foreach ($this->getResourcesSuffixes() as $suffix) {
            if (str_ends_with($resourceName, $suffix)) {
                return substr($resourceName, 0, strlen($resourceName) - strlen($suffix));
            }
        }
        return $resourceName;
    }

    protected function getDiagramSuffixes(): array
    {
        return self::DIAGRAM_SUFFIXES;
    }

    protected function ensureNoDuplicateDefinitionKeys(array $definitions): void
    {
        $keys = [];

        foreach ($definitions as $definition) {
            $key = $definition->getKey();

            if (in_array($key, $keys)) {
                throw new ProcessEngineException("The deployment contains definitions with the same key '" . $key . "' (id attribute), this is not allowed");
            }

            $keys[] = $key;
        }
    }

    protected function postProcessDefinitions(DeploymentEntity $deployment, array $definitions, Properties $properties): void
    {
        if ($deployment->isNew()) {
            // if the deployment is new persist the new definitions
            $this->persistDefinitions($deployment, $definitions, $properties);
        } else {
            // if the current deployment is not a new one,
            // then load the already existing definitions
            $this->loadDefinitions($deployment, $definitions, $properties);
        }
    }

    protected function persistDefinitions(DeploymentEntity $deployment, array $definitions, Properties $properties): void
    {
        foreach ($definitions as $definition) {
            $definitionKey = $definition->getKey();
            $tenantId = $deployment->getTenantId();

            $latestDefinition = $this->findLatestDefinitionByKeyAndTenantId($definitionKey, $tenantId);

            $this->updateDefinitionByLatestDefinition($deployment, $definition, $latestDefinition);

            $this->persistDefinition($definition);
            $this->registerDefinition($deployment, $definition, $properties);
        }
    }

    protected function updateDefinitionByLatestDefinition(DeploymentEntity $deployment, DefinitionEntity $definition, DefinitionEntity $latestDefinition): void
    {
        $definition->setVersion($this->getNextVersion($deployment, $definition, $latestDefinition));
        $definition->setId($this->generateDefinitionId($deployment, $definition, $latestDefinition));
        $definition->setDeploymentId($deployment->getId());
        $definition->setTenantId($deployment->getTenantId());
    }

    protected function loadDefinitions(DeploymentEntity $deployment, array $definitions, Properties $properties): void
    {
        foreach ($definitions as $definition) {
            $deploymentId = $deployment->getId();
            $definitionKey = $definition->getKey();

            $persistedDefinition = $this->findDefinitionByDeploymentAndKey($deploymentId, $definitionKey);
            $this->handlePersistedDefinition($definition, $persistedDefinition, $deployment, $properties);
        }
    }

    protected function handlePersistedDefinition(
        DefinitionEntity $definition,
        ?DefinitionEntity $persistedDefinition,
        DeploymentEntity $deployment,
        Properties $properties
    ): void {
        $this->persistedDefinitionLoaded($deployment, $definition, $persistedDefinition);
        $this->updateDefinitionByPersistedDefinition($deployment, $definition, $persistedDefinition);
        $this->registerDefinition($deployment, $definition, $properties);
    }

    protected function updateDefinitionByPersistedDefinition(DeploymentEntity $deployment, DefinitionEntity $definition, DefinitionEntity $persistedDefinition): void
    {
        $definition->setVersion($persistedDefinition->getVersion());
        $definition->setId($persistedDefinition->getId());
        $definition->setDeploymentId($deployment->getId());
        $definition->setTenantId($persistedDefinition->getTenantId());
    }

    /**
     * Called when a previous version of a definition was loaded from the persistent store.
     *
     * @param deployment the deployment of the definition
     * @param definition the definition entity
     * @param persistedDefinition the loaded definition entity
     */
    protected function persistedDefinitionLoaded(DeploymentEntity $deployment, DefinitionEntity $definition, DefinitionEntity $persistedDefinition): void
    {
        // do nothing;
    }

    /**
     * Find a definition entity by deployment id and definition key.
     * @param deploymentId the deployment id
     * @param definitionKey the definition key
     * @return DefinitionEntity the corresponding definition entity or null if non is found
     */
    abstract protected function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey): DefinitionEntity;

    /**
     * Find the last deployed definition entity by definition key and tenant id.
     *
     * @return DefinitionEntity the corresponding definition entity or null if non is found
     */
    abstract protected function findLatestDefinitionByKeyAndTenantId(string $definitionKey, string $tenantId): DefinitionEntity;

    /**
     * Persist definition entity into the database.
     * @param definition the definition entity
     */
    abstract protected function persistDefinition(DefinitionEntity $definition): void;

    protected function registerDefinition(DeploymentEntity $deployment, DefinitionEntity $definition, Properties $properties): void
    {
        $deploymentCache = $this->getDeploymentCache();

        // Add to cache
        $this->addDefinitionToDeploymentCache($deploymentCache, $definition);

        $this->definitionAddedToDeploymentCache($deployment, $definition, $properties);

        // Add to deployment for further usage
        $deployment->addDeployedArtifact($definition);
    }

    /**
     * Add a definition to the deployment cache
     *
     * @param deploymentCache the deployment cache
     * @param definition the definition to add
     */
    abstract protected function addDefinitionToDeploymentCache(DeploymentCache $deploymentCache, DefinitionEntity $definition): void;

    /**
     * Called after a definition was added to the deployment cache.
     *
     * @param deployment the deployment of the definition
     * @param definition the definition entity
     */
    protected function definitionAddedToDeploymentCache(DeploymentEntity $deployment, DefinitionEntity $definition, Properties $properties): void
    {
        // do nothing
    }

    /**
     * per default we increment the latest definition version by one - but you
     * might want to hook in some own logic here, e.g. to align definition
     * versions with deployment / build versions.
     */
    protected function getNextVersion(DeploymentEntity $deployment, DefinitionEntity $newDefinition, DefinitionEntity $latestDefinition): int
    {
        $result = 1;
        if ($latestDefinition !== null) {
            $latestVersion = $latestDefinition->getVersion();
            $result = $latestVersion + 1;
        }
        return $result;
    }

    /**
     * create an id for the definition. The default is to ask the IdGenerator
     * and add the definition key and version if that does not exceed 64 characters.
     * You might want to hook in your own implementation here.
     */
    protected function generateDefinitionId(DeploymentEntity $deployment, DefinitionEntity $newDefinition, DefinitionEntity $latestDefinition): string
    {
        $nextId = $this->idGenerator->getNextId();

        $definitionKey = $newDefinition->getKey();
        $definitionVersion = $newDefinition->getVersion();

        $definitionId = $definitionKey
            . ":" . $definitionVersion
            . ":" . $nextId;

        // ACT-115: maximum id length is 64 characters
        if (strlen($definitionId) > 64) {
            $definitionId = $nextId;
        }
        return $definitionId;
    }

    protected function getProcessEngineConfiguration(): ?ProcessEngineConfigurationImpl
    {
        return Context::getProcessEngineConfiguration();
    }

    protected function getCommandContext(): ?CommandContext
    {
        return Context::getCommandContext();
    }

    protected function getDeploymentCache(): DeploymentCache
    {
        return $this->getProcessEngineConfiguration()->getDeploymentCache();
    }
}
