<?php

namespace Jabe\Application\Impl;

use Jabe\Application\ProcessApplicationDeploymentInfoInterface;

class ProcessApplicationDeploymentInfoImpl implements ProcessApplicationDeploymentInfoInterface
{
    protected $processEngineName;

    protected $deploymentId;

    public function getProcessEngineName(): ?string
    {
        return $this->processEngineName;
    }

    public function setProcessEngineName(?string $processEngineName): void
    {
        $this->processEngineName = $processEngineName;
    }

    public function getDeploymentId(): ?string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(?string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }
}
