<?php

namespace Jabe\Engine\Impl\Repository;

use Jabe\Engine\ProcessEngineInterface;
use Jabe\Engine\Repository\{
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
