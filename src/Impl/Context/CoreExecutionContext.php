<?php

namespace Jabe\Impl\Context;

use Jabe\Impl\Core\Instance\CoreExecution;
use Jabe\Impl\Persistence\Entity\DeploymentEntity;

abstract class CoreExecutionContext
{
    protected $execution;

    public function __construct(CoreExecution $execution)
    {
        $this->execution = $execution;
    }

    public function getExecution(): CoreExecution
    {
        return $this->execution;
    }

    abstract protected function getDeploymentId(): ?string;

    public function getDeployment(): ?DeploymentEntity
    {
        return Context::getCommandContext()
            ->getDeploymentManager()
            ->findDeploymentById($this->getDeploymentId());
    }
}
