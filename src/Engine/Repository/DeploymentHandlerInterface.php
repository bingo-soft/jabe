<?php

namespace Jabe\Engine\Repository;

use Jabe\Engine\Authorization\ResourceInterface;

interface DeploymentHandlerInterface
{
    public function shouldDeployResource(ResourceInterface $newResource, ResourceInterface $existingResource): bool;

    public function determineDuplicateDeployment(CandidateDeploymentInterface $candidateDeployment): string;

    public function determineDeploymentsToResumeByProcessDefinitionKey(array $processDefinitionKeys): array;

    public function determineDeploymentsToResumeByDeploymentName(
        CandidateDeploymentInterface $candidateDeployment
    ): array;
}
