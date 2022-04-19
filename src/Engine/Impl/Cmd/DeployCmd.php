<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Application\{
    ProcessApplicationReferenceInterface,
    ProcessApplicationRegistrationInterface
};
use BpmPlatform\Engine\{
    ProcessEngineInterface,
    RepositoryServiceInterface
};
use BpmPlatform\Engine\Exception\{
    NotFoundException,
    NotValidException
};
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Deployer\BpmnDeployer;
use BpmPlatform\Engine\Impl\Cfg\{
    TransactionLogger,
    TransactionState
};
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Deploy\DeploymentFailListener;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    DeploymentManager,
    ProcessApplicationDeploymentImpl,
    ProcessDefinitionManager,
    PropertyChange,
    ResourceEntity,
    ResourceManager,
    UserOperationLogManager
};
use BpmPlatform\Engine\Impl\Repository\{
    CandidateDeploymentImpl,
    DeploymentBuilderImpl,
    ProcessApplicationDeploymentBuilderImpl
};
use BpmPlatform\Engine\Impl\Util\{
    ClockUtil,
    StringUtil
};
use BpmPlatform\Engine\Repository\{
    CandidateDeploymentInterface,
    DeploymentInterface,
    DeploymentHandlerInterface,
    DeploymentWithDefinitionsInterface,
    ProcessApplicationDeploymentInterface,
    ProcessApplicationDeploymentBuilderInterface,
    ProcessDefinitionInterface,
    ResourceInterface,
    ResumePreviousBy
};
use BpmPlatform\Model\Bpmn\{
    Bpmn,
    BpmnModelInstanceInterface
};
use BpmPlatform\Model\Bpmn\Instance\ProcessInterface;

class DeployCmd implements CommandInterface
{
    //private static final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;
    //private static final TransactionLogger TX_LOG = ProcessEngineLogger.TX_LOGGER;

    protected $deploymentBuilder;
    protected $deploymentHandler;

    public function __construct(DeploymentBuilderImpl $deploymentBuilder)
    {
        $this->deploymentBuilder = $deploymentBuilder;
    }

    public function execute(CommandContext $commandContext)
    {
        return $this->doExecute($commandContext);
    }

    protected function doExecute(CommandContext $commandContext): DeploymentWithDefinitionsInterface
    {
        $deploymentManager = $commandContext->getDeploymentManager();

        // load deployment handler
        $processEngine = $commandContext->getProcessEngineConfiguration()->getProcessEngine();
        $this->deploymentHandler = $commandContext->getProcessEngineConfiguration()
            ->getDeploymentHandlerFactory()
            ->buildDeploymentHandler($processEngine);

        $deploymentIds = $this->getAllDeploymentIds($this->deploymentBuilder);
        if (!empty($deploymentIds)) {
            $deploymentIdArray = $deploymentIds;
            $deployments = $deploymentManager->findDeploymentsByIds($deploymentIdArray);
            $this->ensureDeploymentsWithIdsExists($deploymentIds, $deployments);
        }

        $this->checkCreateAndReadDeployments($commandContext, $deploymentIds);

        // set deployment name if it should retrieved from an existing deployment
        $nameFromDeployment = $this->deploymentBuilder->getNameFromDeployment();
        $this->setDeploymentName($nameFromDeployment, $this->deploymentBuilder, $commandContext);

        // get resources to re-deploy
        $resources = $this->getResources($this->deploymentBuilder, $commandContext);
        // .. and add them the builder
        $this->addResources($resources, $this->deploymentBuilder);

        $resourceNames = $this->deploymentBuilder->getResourceNames();
        if (empty($resourceNames)) {
            throw new NotValidException("No deployment resources contained to deploy.");
        }

        // perform deployment
        $self = $this;
        $deployment = $commandContext->runWithoutAuthorization(function () use ($self, $commandContext) {
            $self->acquireExclusiveLock($commandContext);
            $deploymentToRegister = $self->initDeployment();
            $resourcesToDeploy = $self->resolveResourcesToDeploy($commandContext, $deploymentToRegister);
            $resourcesToIgnore = $deploymentToRegister->getResources();
            foreach (array_keys($resourcesToDeploy) as $key) {
                if (array_key_exists($key, $resourcesToIgnore)) {
                    unset($resourcesToIgnore[$key]);
                }
            }
            // save initial deployment resources before they are replaced with only the deployed ones
            $candidateDeployment =
                CandidateDeploymentImpl::fromDeploymentEntity($deploymentToRegister);
            if (!empty($resourcesToDeploy)) {
                //LOG.debugCreatingNewDeployment();
                $deploymentToRegister->setResources($resourcesToDeploy);
                $self->deploy($commandContext, $deploymentToRegister);
            } else {
                // if there are no resources to be deployed, find an existing deployment
                $duplicateDeploymentId = $self->deploymentHandler->determineDuplicateDeployment($candidateDeployment);
                $deploymentToRegister = $commandContext->getDeploymentManager()->findDeploymentById($duplicateDeploymentId);
            }

            $self->scheduleProcessDefinitionActivation($commandContext, $deploymentToRegister);

            if ($self->deploymentBuilder instanceof ProcessApplicationDeploymentBuilderInterface) {
                // for process application deployments, job executor registration
                // is managed by the ProcessApplicationManager
                $registration = $self->registerProcessApplication(
                    $commandContext,
                    $deploymentToRegister,
                    $candidateDeployment,
                    array_values($resourcesToIgnore)
                );

                return new ProcessApplicationDeploymentImpl($deploymentToRegister, $registration);
            } else {
                $self->registerWithJobExecutor($commandContext, $deploymentToRegister);
            }

            return $deploymentToRegister;
        });

        $this->createUserOperationLog($this->deploymentBuilder, $deployment, $commandContext);

        return $deployment;
    }

    protected function acquireExclusiveLock(CommandContext $commandContext): void
    {
        if ($commandContext->getProcessEngineConfiguration()->isDeploymentLockUsed()) {
            // Acquire global exclusive lock: this ensures that there can be only one
            // transaction in the cluster which is allowed to perform deployments.
            // This is important to ensure that duplicate filtering works correctly
            // in a multi-node cluster. See also https://app.camunda.com/jira/browse/CAM-2128

            // It is also important to ensure the uniqueness of a process definition key,
            // version and tenant-id since there is no database constraint to check it.

            $commandContext->getPropertyManager()->acquireExclusiveLock();
        } else {
            //LOG.warnDisabledDeploymentLock();
        }
    }

    protected function resolveResourcesToDeploy(
        CommandContext $commandContext,
        DeploymentEntity $candidateDeployment
    ): array {

        $resourcesToDeploy = [];
        $candidateResources = $candidateDeployment->getResources();

        if ($this->deploymentBuilder->isDuplicateFilterEnabled()) {
            $source = $candidateDeployment->getSource();
            if ($source == null || empty($source)) {
                $source = ProcessApplicationDeploymentInterface::PROCESS_APPLICATION_DEPLOYMENT_SOURCE;
            }

            $existingResources = $commandContext
                ->getResourceManager()
                ->findLatestResourcesByDeploymentName(
                    $candidateDeployment->getName(),
                    array_keys($candidateResources),
                    $source,
                    $candidateDeployment->getTenantId()
                );

            foreach ($candidateResources as $key => $deployedResource) {
                $resourceName = $deployedResource->getName();
                $existingResource = null;
                if (array_key_exists($resourceName, $existingResources)) {
                    $existingResource = $existingResources[$resourceName];
                }

                if (
                    $existingResource == null
                    || $existingResource->isGenerated()
                    || $this->deploymentHandler->shouldDeployResource($deployedResource, $existingResource)
                ) {
                    if ($this->deploymentBuilder->isDeployChangedOnly()) {
                        // resource should be deployed
                        $resourcesToDeploy[$resourceName] = $deployedResource;
                    } else {
                        // all resources should be deployed
                        $resourcesToDeploy = $candidateResources;
                        break;
                    }
                }
            }
        } else {
            $resourcesToDeploy = $candidateResources;
        }

        return $resourcesToDeploy;
    }

    protected function deploy(CommandContext $commandContext, DeploymentEntity $deployment): void
    {
        $deployment->setNew(true);
        $commandContext->getDeploymentManager()->insertDeployment($deployment);
    }

    protected function scheduleProcessDefinitionActivation(
        CommandContext $commandContext,
        DeploymentWithDefinitionsInterface $deployment
    ): void {

        if ($deploymentBuilder->getProcessDefinitionsActivationDate() != null) {
            $repositoryService = $commandContext->getProcessEngineConfiguration()
                ->getRepositoryService();

            foreach ($this->getDeployedProcesses($commandContext, $deployment) as $processDefinition) {
                // If activation date is set, we first suspend all the process definition
                $repositoryService
                    ->updateProcessDefinitionSuspensionState()
                    ->byProcessDefinitionId($processDefinition->getId())
                    ->suspend();

                // And we schedule an activation at the provided date
                $repositoryService
                    ->updateProcessDefinitionSuspensionState()
                    ->byProcessDefinitionId($processDefinition->getId())
                    ->executionDate($deploymentBuilder->getProcessDefinitionsActivationDate())
                    ->activate();
            }
        }
    }

    protected function registerProcessApplication(
        CommandContext $commandContext,
        DeploymentEntity $deploymentToRegister,
        CandidateDeploymentInterface $candidateDeployment,
        array $ignoredResources
    ): ProcessApplicationRegistrationInterface {

        $appDeploymentBuilder = $this->deploymentBuilder;
        $appReference = $appDeploymentBuilder->getProcessApplicationReference();

        // build set of deployment ids this process app should be registered for:
        $deploymentsToRegister = [$deploymentToRegister->getId()];
        if ($appDeploymentBuilder->isResumePreviousVersions()) {
            $resumePreviousBy = $appDeploymentBuilder->getResumePreviousVersionsBy();
            switch ($resumePreviousBy) {
                case ResumePreviousBy::RESUME_BY_DEPLOYMENT_NAME:
                    $deploymentsToRegister = array_merge(
                        $deploymentsToRegister,
                        $this->deploymentHandler->determineDeploymentsToResumeByDeploymentName($candidateDeployment)
                    );
                    break;

                case ResumePreviousBy::RESUME_BY_PROCESS_DEFINITION_KEY:
                default:
                    $processDefinitionKeys = $this->getProcessDefinitionsFromResources(
                        $commandContext,
                        $deploymentToRegister,
                        $ignoredResources
                    );

                    // only determine deployments to resume if there are actual process definitions to look for
                    if (count($processDefinitionKeys) > 0) {
                        $deploymentsToRegister = array_merge(
                            $deploymentsToRegister,
                            $this->deploymentHandler->determineDeploymentsToResumeByProcessDefinitionKey($processDefinitionKeys)
                        );
                    }
                    break;
            }
        }

        // register process application for deployments
        return (new RegisterProcessApplicationCmd($deploymentsToRegister, $appReference))->execute($commandContext);
    }

    protected function registerWithJobExecutor(CommandContext $commandContext, DeploymentInterface $deployment): void
    {
        try {
            (new RegisterDeploymentCmd($deployment->getId()))->execute($commandContext);
        } finally {
            $listener = new DeploymentFailListener(
                $deployment->getId(),
                $commandContext->getProcessEngineConfiguration()->getCommandExecutorTxRequiresNew()
            );
            try {
                $commandContext->getTransactionContext()->addTransactionListener(TransactionState::ROLLED_BACK, $listener);
            } catch (\Exception $e) {
                //TX_LOG.debugTransactionOperation("Could not register transaction synchronization. Probably the TX has already been rolled back by application code.");
                $listener->execute($commandContext);
            }
        }
    }

    // setters, initializers etc.

    protected function createUserOperationLog(DeploymentBuilderImpl $deploymentBuilder, DeploymentInterface $deployment, CommandContext $commandContext): void
    {
        $logManager = $commandContext->getOperationLogManager();
        $properties = [];

        $filterDuplicate = new PropertyChange("duplicateFilterEnabled", null, $deploymentBuilder->isDuplicateFilterEnabled());
        $properties[] = $filterDuplicate;

        if ($deploymentBuilder->isDuplicateFilterEnabled()) {
            $deployChangedOnly = new PropertyChange("deployChangedOnly", null, $deploymentBuilder->isDeployChangedOnly());
            $properties[] = $deployChangedOnly;
        }

        $logManager->logDeploymentOperation(UserOperationLogEntryInterface::OPERATION_TYPE_CREATE, $deployment->getId(), $properties);
    }

    protected function initDeployment(): DeploymentEntity
    {
        $deployment = $deploymentBuilder->getDeployment();
        $deployment->setDeploymentTime(ClockUtil::getCurrentTime());
        return $deployment;
    }

    protected function setDeploymentName(?string $deploymentId, DeploymentBuilderImpl $deploymentBuilder, CommandContext $commandContext): void
    {
        if (!empty($deploymentId)) {
            $deploymentManager = $commandContext->getDeploymentManager();
            $deployment = $deploymentManager->findDeploymentById($deploymentId);
            $deploymentBuilder->getDeployment()->setName($deployment->getName());
        }
    }

    protected function addResources(array $resources, DeploymentBuilderImpl $deploymentBuilder): void
    {
        $deployment = $deploymentBuilder->getDeployment();
        $existingResources = $deployment->getResources();

        foreach ($resources as $resource) {
            $resourceName = $resource->getName();

            if (!empty($existingResources) && array_key_exists($resourceName, $existingResources)) {
                $message = sprintf("Cannot add resource with id '%s' and name '%s' from "
                    . "deployment with id '%s' to new deployment because the new deployment contains "
                    . "already a resource with same name.", $resource->getId(), $resourceName, $resource->getDeploymentId());

                throw new NotValidException($message);
            }

            $inputStream = $resource->getBytes();
            $deploymentBuilder->addInputStream($resourceName, $inputStream);
        }
    }

    // getters

    protected function getMissingElements(array $expected, array $actual): array
    {
        $missingElements = [];
        foreach ($expected as $value) {
            if (!array_key_exists($value, $actual)) {
                $missingElements[] = $value;
            }
        }
        return $missingElements;
    }

    protected function getResources(DeploymentBuilderImpl $deploymentBuilder, CommandContext $commandContext): array
    {
        $resources = [];

        $deploymentIds = $deploymentBuilder->getDeployments();
        $resources = $this->getResourcesByDeploymentId($deploymentIds, $commandContext);

        $deploymentResourcesById = $deploymentBuilder->getDeploymentResourcesById();
        $resources = array_merge($resources, $this->getResourcesById($deploymentResourcesById, $commandContext));

        $deploymentResourcesByName = $deploymentBuilder->getDeploymentResourcesByName();
        $resources = array_merge($resources, $this->getResourcesByName($deploymentResourcesByName, $commandContext));

        $this->checkDuplicateResourceName($resources);

        return $resources;
    }

    protected function getResourcesByDeploymentId(array $deploymentIds, CommandContext $commandContext): array
    {
        $result = [];

        if (!empty($deploymentIds)) {
            $deploymentManager = $commandContext->getDeploymentManager();

            foreach ($deploymentIds as $deploymentId) {
                $deployment = $deploymentManager->findDeploymentById($deploymentId);
                $resources = $deployment->getResources();
                $values = array_values($resources);
                $result = array_merge($result, $values);
            }
        }

        return $result;
    }

    protected function getResourcesById(array $resourcesById, CommandContext $commandContext): array
    {
        $result = [];

        $resourceManager = $commandContext->getResourceManager();

        foreach (array_keys($resourcesById) as $deploymentId) {
            $resourceIds = $resourcesById[$deploymentId];

            $resourceIdArray = $resourceIds;
            $resources = $resourceManager->findResourceByDeploymentIdAndResourceIds($deploymentId, $resourceIdArray);

            $this->ensureResourcesWithIdsExist($deploymentId, $resourceIds, $resources);

            $result = array_merge($result, $resources);
        }

        return $result;
    }

    protected function getResourcesByName(array $resourcesByName, CommandContext $commandContext): array
    {
        $result = [];

        $resourceManager = $commandContext->getResourceManager();

        foreach (array_keys($resourcesByName) as $deploymentId) {
            $resourceNames = $resourcesByName[$deploymentId];

            $resourceNameArray = $resourceNames;
            $resources = $resourceManager->findResourceByDeploymentIdAndResourceNames($deploymentId, $resourceNameArray);

            $this->ensureResourcesWithNamesExist($deploymentId, $resourceNames, $resources);

            $result = array_merge($result, $resources);
        }
        return $result;
    }

    protected function getDeployedProcesses(CommandContext $commandContext, DeploymentWithDefinitionsInterface $deployment): array
    {
        $deployedProcessDefinitions = $deployment->getDeployedProcessDefinitions();
        if (empty($deployedProcessDefinitions)) {
            // existing deployment
            $manager = $commandContext->getProcessDefinitionManager();
            $deployedProcessDefinitions = $manager->findProcessDefinitionsByDeploymentId($deployment->getId());
        }
        return $deployedProcessDefinitions;
    }

    protected function getProcessDefinitionsFromResources(
        CommandContext $commandContext,
        DeploymentEntity $deploymentToRegister,
        array $ignoredResources
    ): array {

        $processDefinitionKeys = [];

        // get process definition keys for already available process definitions
        $processDefinitionKeys = $this->parseProcessDefinitionKeys($ignoredResources);

        // get process definition keys for updated process definitions
        foreach ($this->getDeployedProcesses($commandContext, $deploymentToRegister) as $processDefinition) {
            if ($processDefinition->getVersion() > 1) {
                $processDefinitionKeys[] = $processDefinition->getKey();
            }
        }

        return $processDefinitionKeys;
    }

    protected function parseProcessDefinitionKeys(array $resources): array
    {
        $processDefinitionKeys = [];

        foreach ($resources as $resource) {
            if ($this->isBpmnResource($resource)) {
                $byteStream = $resource->getBytes();
                $model = Bpmn::readModelFromStream($byteStream);
                foreach ($model->getDefinitions()->getChildElementsByType(ProcessInterface::class) as $process) {
                    $processDefinitionKeys[] = $process->getId();
                }
            }
            /*elseif (isCmmnResource(resource)) {
                ByteArrayInputStream byteStream = new ByteArrayInputStream(resource.getBytes());
                CmmnModelInstance model = Cmmn.readModelFromStream(byteStream);
                for (Case cmmnCase : model.getDefinitions().getCases()) {
                    processDefinitionKeys.add(cmmnCase.getId());
                }
            }*/
        }
        return $processDefinitionKeys;
    }

    protected function getAllDeploymentIds(DeploymentBuilderImpl $deploymentBuilder): array
    {
        $result = [];

        $nameFromDeployment = $deploymentBuilder->getNameFromDeployment();
        if (!empty($nameFromDeployment)) {
            $result[] = $nameFromDeployment;
        }

        $deployments = $deploymentBuilder->getDeployments();
        $result = array_merge($result, $deployments);

        $deployments = array_keys($deploymentBuilder->getDeploymentResourcesById());
        $result = array_merge($result, $deployments);

        $deployments = array_keys($deploymentBuilder->getDeploymentResourcesByName());
        $result = array_merge($result, $deployments);

        return $result;
    }

    // checkers

    protected function checkDuplicateResourceName(array $resources): void
    {
        $resourceMap = [];

        foreach ($resources as $resource) {
            $name = $resource->getName();

            if (array_key_exists($name, $resourceMap)) {
                $duplicate = $resourceMap[$name];
                $deploymentId = $resource->getDeploymentId();
                if ($deploymentId != $duplicate->getDeploymentId()) {
                    $message = sprintf("The deployments with id '%s' and '%s' contain a resource with same name '%s'.", $deploymentId, $duplicate->getDeploymentId(), $name);
                    throw new NotValidException($message);
                }
            }
            $resourceMap[$name] = $resource;
        }
    }

    protected function checkCreateAndReadDeployments(CommandContext $commandContext, array $deploymentIds): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateDeployment();
            foreach ($deploymentIds as $deploymentId) {
                $checker->checkReadDeployment($deploymentId);
            }
        }
    }

    protected function isBpmnResource(ResourceInterface $resourceEntity): bool
    {
        return StringUtil::hasAnySuffix($resourceEntity->getName(), BpmnDeployer::BPMN_RESOURCE_SUFFIXES);
    }

    /*protected function isCmmnResource(Resource resourceEntity): bool
    {
        return StringUtil.hasAnySuffix(resourceEntity.getName(), CmmnDeployer.CMMN_RESOURCE_SUFFIXES);
    }*/

    // ensures
    protected function ensureDeploymentsWithIdsExists(array $expected, array $actual): void
    {
        $deploymentMap = [];
        foreach ($actual as $deployment) {
            $deploymentMap[$deployment->getId()] = $deployment;
        }

        $missingDeployments = $this->getMissingElements($expected, $deploymentMap);

        if (!empty($missingDeployments)) {
            $builder = "";

            $builder .= "The following deployments are not found by id: ";
            $builder .= StringUtil::join($missingDeployments);

            throw new NotFoundException($builder);
        }
    }

    protected function ensureResourcesWithIdsExist(string $deploymentId, array $expectedIds, array $actual): void
    {
        $resources = [];
        foreach ($actual as $resource) {
            $resources[$resource->getId()] = $resource;
        }
        $this->ensureResourcesWithKeysExist($deploymentId, $expectedIds, $resources, "id");
    }

    protected function ensureResourcesWithNamesExist(string $deploymentId, array $expectedNames, array $actual): void
    {
        $resources = [];
        foreach ($actual as $resource) {
            $resources[$resource->getName()] = $resource;
        }
        $this->ensureResourcesWithKeysExist($deploymentId, $expectedNames, $resources, "name");
    }

    protected function ensureResourcesWithKeysExist(string $deploymentId, array $expectedKeys, array $actual, string $valueProperty): void
    {
        $missingResources = $this->getMissingElements($expectedKeys, $actual);

        if (!empty($missingResources)) {
            $builder = "";
            $builder .= "The deployment with id '";
            $builder .= $deploymentId;
            $builder .= "' does not contain the following resources with ";
            $builder .= $valueProperty;
            $builder .= ": ";
            $builder .= StringUtil::join($missingResources);
            throw new NotFoundException($builder);
        }
    }

    public function isRetryable(): bool
    {
        return true;
    }
}
