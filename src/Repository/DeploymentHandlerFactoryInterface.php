<?php

namespace Jabe\Repository;

use Jabe\ProcessEngineInterface;

interface DeploymentHandlerFactoryInterface
{
    /**
     * Creates a {@link DeploymentHandlerInterface} instance.
     *
     * @param processEngine is the {@link ProcessEngine} where the Deployment is deployed to.
     * @return the {@link DeploymentHandlerInterface} implementation.
     */
    public function buildDeploymentHandler(ProcessEngineInterface $processEngine): DeploymentHandlerInterface;
}
