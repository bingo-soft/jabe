<?php

namespace Jabe\Impl\Repository;

use Jabe\{
    ProcessEngineInterface,
    RepositoryServiceInterface
};
use Jabe\Impl\Context\Context;
use Jabe\Repository\{
    CandidateDeploymentInterface,
    DeploymentInterface,
    DeploymentHandlerInterface,
    ProcessDefinitionInterface,
    ResourceInterface
};

class DefaultDeploymentHandler implements DeploymentHandlerInterface
{
    protected $processEngine;
    protected $repositoryService;

    public function __construct(ProcessEngineInterface $processEngine)
    {
        $this->processEngine = $processEngine;
        $this->repositoryService = $processEngine->getRepositoryService();
    }

    public function shouldDeployResource(ResourceInterface $newResource, ResourceInterface $existingResource): bool
    {
        return $this->resourcesDiffer($newResource, $existingResource);
    }

    public function determineDuplicateDeployment(CandidateDeploymentInterface $candidateDeployment): string
    {
        return Context::getCommandContext()
            ->getDeploymentManager()
            ->findLatestDeploymentByName($candidateDeployment->getName())
            ->getId();
    }

    public function determineDeploymentsToResumeByProcessDefinitionKey(array $processDefinitionKeys): array
    {

        $deploymentIds = [];
        $processDefinitions = Context::getCommandContext()->getProcessDefinitionManager()
            ->findProcessDefinitionsByKeyIn($processDefinitionKeys);
        foreach ($processDefinitions as $processDefinition) {
            $deploymentIds[] = $processDefinition->getDeploymentId();
        }

        return array_unique($deploymentIds);
    }

    public function determineDeploymentsToResumeByDeploymentName(CandidateDeploymentInterface $candidateDeployment): array
    {
        $previousDeployments = $this->processEngine->getRepositoryService()
            ->createDeploymentQuery()
            ->deploymentName($candidateDeployment->getName())
            ->list();

        $deploymentIds = [];
        foreach ($previousDeployments as $deployment) {
            $deploymentIds[] = $deployment->getId();
        }

        return array_unique($deploymentIds);
    }

    protected function resourcesDiffer(ResourceInterface $resource, ResourceInterface $existing): bool
    {
        $bytes = $resource->getBytes();
        $savedBytes = $existing->getBytes();
        return $bytes != $savedBytes;
    }
}
