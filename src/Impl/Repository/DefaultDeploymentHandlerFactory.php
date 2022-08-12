<?php

namespace Jabe\Impl\Repository;

use Jabe\ProcessEngineInterface;
use Jabe\Repository\{
    DeploymentHandlerInterface,
    DeploymentHandlerFactoryInterface
};

class DefaultDeploymentHandlerFactory implements DeploymentHandlerFactoryInterface
{
    public function buildDeploymentHandler(ProcessEngineInterface $processEngine): DeploymentHandlerInterface
    {
        return new DefaultDeploymentHandler($processEngine);
    }
}
