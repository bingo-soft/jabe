<?php

namespace Jabe\Impl\Repository;

use Jabe\ProcessEngineException;
use Jabe\Exception\NotValidException;
use Jabe\Impl\{
    ProcessEngineLogger,
    RepositoryServiceImpl
};
use Jabe\Impl\Bpmn\Deployer\BpmnDeployer;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ResourceEntity
};
use Jabe\Impl\Util\{
    CollectionUtil,
    EnsureUtil,
    IoUtil,
    ReflectUtil,
    StringUtil
};
use Jabe\Repository\{
    DeploymentInterface,
    DeploymentBuilderInterface,
    DeploymentWithDefinitionsInterface
};
use Bpmn\{
    Bpmn,
    BpmnModelInstanceInterface
};

class DeploymentBuilderImpl implements DeploymentBuilderInterface, \Serializable
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $repositoryService;
    protected $deployment;
    protected $isDuplicateFilterEnabled = false;
    protected $deployChangedOnly = false;
    protected $processDefinitionsActivationDate;

    protected $nameFromDeployment;
    protected $deployments = [];
    protected $deploymentResourcesById = [];
    protected $deploymentResourcesByName = [];

    public function __construct(RepositoryServiceImpl $repositoryService)
    {
        $this->deployment = new DeploymentEntity();
        $this->repositoryService = $repositoryService;
    }

    public function serialize()
    {
        return json_encode([
            'deployment' => serialize($this->deployment),
            'isDuplicateFilterEnabled' => $this->isDuplicateFilterEnabled,
            'deployChangedOnly' => $this->deployChangedOnly,
            'processDefinitionsActivationDate' => $this->processDefinitionsActivationDate,
            'nameFromDeployment' => $this->nameFromDeployment,
            'deployments' => $this->deployments,
            'deploymentResourcesById' => $this->deploymentResourcesById,
            'deploymentResourcesByName' => $this->deploymentResourcesByName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->deployment = unserialize($json->deployment);
        $this->isDuplicateFilterEnabled = $json->isDuplicateFilterEnabled;
        $this->deployChangedOnly = $json->deployChangedOnly;
        $this->processDefinitionsActivationDate = $json->processDefinitionsActivationDate;
        $this->nameFromDeployment = $json->nameFromDeployment;
        $this->deployments = $json->deployments;
        $this->deploymentResourcesById = $json->deploymentResourcesById;
        $this->deploymentResourcesByName = $json->deploymentResourcesByName;
    }

    public function addInputStream(string $resourceName, $inputStream): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull("inputStream for resource '" . $resourceName . "' is null", "inputStream", $inputStream);
        $bytes = IoUtil::readInputStream($inputStream, $resourceName);

        return $this->addBytes($resourceName, $bytes);
    }

    public function addClasspathResource(string $resource): DeploymentBuilderInterface
    {
        $inputStream = ReflectUtil::getResourceAsStream($resource);
        EnsureUtil::ensureNotNull("resource '" . $resource . "' not found", "inputStream", $inputStream);
        return $this->addInputStream($resource, $inputStream);
    }

    public function addString(string $resourceName, string $text): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull("text", "text", $text);

        $bytes = $text;

        return $this->addBytes($resourceName, $bytes);
    }

    /*public DeploymentBuilder addModelInstance(string $resourceName, CmmnModelInstance modelInstance) {
        EnsureUtil::ensureNotNull("modelInstance", modelInstance);

        validateResouceName(resourceName, CmmnDeployer.CMMN_RESOURCE_SUFFIXES);

        ByteArrayOutputStream outputStream = new ByteArrayOutputStream();
        Cmmn.writeModelToStream(outputStream, modelInstance);

        return addBytes(resourceName, outputStream.toByteArray());
    }*/

    public function addModelInstance(string $resourceName, BpmnModelInstanceInterface $modelInstance): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull("modelInstance", "modelInstance", $modelInstance);

        $this->validateResouceName($resourceName, BpmnDeployer::BPMN_RESOURCE_SUFFIXES);

        $path = tempnam(sys_get_temp_dir(), 'bpmn');
        $outputStream = fopen($path, 'a+');

        Bpmn::writeModelToStream($outputStream, $modelInstance);

        return $this->addBytes($resourceName, file_get_contents($path));
    }

    /*public DeploymentBuilder addModelInstance(string $resourceName, DmnModelInstance modelInstance) {
        EnsureUtil::ensureNotNull("modelInstance", modelInstance);

        validateResouceName(resourceName, DecisionDefinitionDeployer.DMN_RESOURCE_SUFFIXES);

        ByteArrayOutputStream outputStream = new ByteArrayOutputStream();
        Dmn.writeModelToStream(outputStream, modelInstance);

        return addBytes(resourceName, outputStream.toByteArray());
    }*/

    private function validateResouceName(string $resourceName, array $resourceSuffixes): void
    {
        if (!StringUtil::hasAnySuffix($resourceName, $resourceSuffixes)) {
            //LOG.warnDeploymentResourceHasWrongName(resourceName, resourceSuffixes);
        }
    }

    protected function addBytes(string $resourceName, string $bytes): DeploymentBuilderInterface
    {
        $resource = new ResourceEntity();
        $resource->setBytes($bytes);
        $resource->setName($resourceName);
        $this->deployment->addResource($resource);

        return $this;
    }

    /*public function addZipInputStream(ZipInputStream zipInputStream): DeploymentBuilderInterface
    {
        try {
            ZipEntry entry = zipInputStream.getNextEntry();
            while (entry !== null) {
                if (!entry.isDirectory()) {
                    String entryName = entry.getName();
                    addInputStream(entryName, zipInputStream);
                }
                entry = zipInputStream.getNextEntry();
            }
        } catch (Exception e) {
            throw new ProcessEngineException("problem reading zip input stream", e);
        }
        return $this;
    }*/

    public function addDeploymentResources(string $deploymentId): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);
        $this->deployments[] = $deploymentId;
        return $this;
    }

    public function addDeploymentResourceById(string $deploymentId, string $resourceId): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);
        EnsureUtil::ensureNotNull(NotValidException::class, "resourceId", $resourceId);

        CollectionUtil::addToMapOfSets($this->deploymentResourcesById, $deploymentId, $resourceId);

        return $this;
    }

    public function addDeploymentResourcesById(string $deploymentId, array $resourceIds): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);

        EnsureUtil::ensureNotNull(NotValidException::class, "resourceIds", $resourceIds);
        EnsureUtil::ensureNotEmpty(NotValidException::class, "resourceIds", $resourceIds);
        EnsureUtil::ensureNotContainsNull(NotValidException::class, "resourceIds", $resourceIds);

        CollectionUtil::addCollectionToMapOfSets($this->deploymentResourcesById, $deploymentId, $resourceIds);

        return $this;
    }

    public function addDeploymentResourceByName(string $deploymentId, string $resourceName): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);
        EnsureUtil::ensureNotNull(NotValidException::class, "resourceName", $resourceName);

        CollectionUtil::addToMapOfSets($this->deploymentResourcesByName, $deploymentId, $resourceName);

        return $this;
    }

    public function addDeploymentResourcesByName(string $deploymentId, array $resourceNames): DeploymentBuilderInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "deploymentId", $deploymentId);

        EnsureUtil::ensureNotNull(NotValidException::class, "resourceNames", $resourceNames);
        EnsureUtil::ensureNotEmpty(NotValidException::class, "resourceNames", $resourceNames);
        EnsureUtil::ensureNotContainsNull(NotValidException::class, "resourceNames", $resourceNames);

        CollectionUtil::addCollectionToMapOfSets($this->deploymentResourcesByName, $deploymentId, $resourceNames);

        return $this;
    }

    public function name(string $name): DeploymentBuilderInterface
    {
        if (!empty($this->nameFromDeployment)) {
            $message = sprintf("Cannot set the deployment name to '%s', because the property 'nameForDeployment' has been already set to '%s'.", $name, $this->nameFromDeployment);
            throw new NotValidException($message);
        }
        $this->deployment->setName($name);
        return $this;
    }

    public function nameFromDeployment(string $deploymentId): DeploymentBuilderInterface
    {
        $name = $this->deployment->getName();
        if (!empty($name)) {
            $message = sprintf("Cannot set the given deployment id '%s' to get the name from it, because the deployment name has been already set to '%s'.", $deploymentId, $name);
            throw new NotValidException($message);
        }
        $this->nameFromDeployment = $deploymentId;
        return $this;
    }

    public function enableDuplicateFiltering(bool $deployChangedOnly = false): DeploymentBuilderInterface
    {
        $this->isDuplicateFilterEnabled = true;
        $this->deployChangedOnly = $deployChangedOnly;
        return $this;
    }

    public function activateProcessDefinitionsOn(string $date): DeploymentBuilderInterface
    {
        $this->processDefinitionsActivationDate = $date;
        return $this;
    }

    public function source(string $source): DeploymentBuilderInterface
    {
        $this->deployment->setSource($source);
        return $this;
    }

    public function tenantId(string $tenantId): DeploymentBuilderInterface
    {
        $this->deployment->setTenantId($tenantId);
        return $this;
    }

    public function deploy(): DeploymentInterface
    {
        return $this->deployWithResult();
    }

    public function deployWithResult(): DeploymentWithDefinitionsInterface
    {
        return $this->repositoryService->deployWithResult($this);
    }

    public function getResourceNames(): array
    {
        if (empty($this->deployment->getResources())) {
            return [];
        } else {
            return array_keys($this->deployment->getResources());
        }
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getDeployment(): DeploymentEntity
    {
        return $this->deployment;
    }

    public function isDuplicateFilterEnabled(): bool
    {
        return $this->isDuplicateFilterEnabled;
    }

    public function isDeployChangedOnly(): bool
    {
        return $this->deployChangedOnly;
    }

    public function getProcessDefinitionsActivationDate(): string
    {
        return $this->processDefinitionsActivationDate;
    }

    public function getNameFromDeployment(): string
    {
        return $this->nameFromDeployment;
    }

    public function getDeployments(): array
    {
        return $this->deployments;
    }

    public function getDeploymentResourcesById(): array
    {
        return $this->deploymentResourcesById;
    }

    public function getDeploymentResourcesByName(): array
    {
        return $this->deploymentResourcesByName;
    }
}
