<?php

namespace Jabe\Impl\Repository;

use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ResourceEntity
};
use Jabe\Repository\{
    CandidateDeploymentInterface,
    ResourceInterface
};

class CandidateDeploymentImpl implements CandidateDeploymentInterface
{
    protected $name;
    protected $resources = [];

    public function __construct(string $name, array $resources)
    {
        $this->name = $name;
        $this->resources = $resources;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function setResources(array $resources): void
    {
        $this->resources = $resources;
    }

    public static function fromDeploymentEntity(DeploymentEntity $deploymentEntity): CandidateDeploymentImpl
    {
        // first cast ResourceEntity map to Resource
        $resources = $deploymentEntity->getResources();
        return new CandidateDeploymentImpl($deploymentEntity->getName(), $resources);
    }
}
