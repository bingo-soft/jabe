<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ResourceEntity
};

class CacheDeployer
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $deployers;

    public function __construct()
    {
        $this->deployers = [];
    }

    public function setDeployers(array $deployers): void
    {
        $this->deployers = $deployers;
    }

    public function deploy(DeploymentEntity $deployment): void
    {
        $scope = $this;
        Context::getCommandContext()->runWithoutAuthorization(function () use ($deployment, $scope) {
            foreach ($scope->deployers as $deployer) {
                $deployer->deploy($deployment);
            }
            return null;
        });
    }

    public function deployOnlyGivenResourcesOfDeployment(DeploymentEntity $deployment, array $resourceNames): void
    {
        $this->initDeployment($deployment, $resourceNames);
        $scope = $this;
        Context::getCommandContext()->runWithoutAuthorization(function () use ($deployment, $scope) {
            foreach ($scope->deployers as $deployer) {
                $deployer->deploy($deployment);
            }
            return null;
        });
        $deployment->setResources(null);
    }

    protected function initDeployment(DeploymentEntity $deployment, array $resourceNames): void
    {
        $deployment->clearResources();
        foreach ($resourceNames as $resourceName) {
            if ($resourceName !== null) {
                // with the given resource we prevent the deployment of querying
                // the database which means using all resources that were utilized during the deployment
                $resource = Context::getCommandContext()->getResourceManager()->findResourceByDeploymentIdAndResourceName($deployment->getId(), $resourceName);

                $deployment->addResource($resource);
            }
        }
    }
}
