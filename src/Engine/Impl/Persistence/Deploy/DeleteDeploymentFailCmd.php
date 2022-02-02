<?php

namespace BpmPlatform\Engine\Impl\Persistence\Deploy;

use BpmPlatform\Engine\Application\ProcessApplicationReferenceInterface;
use BpmPlatform\Engine\Impl\Cmd\{
    RegisterDeploymentCmd,
    RegisterProcessApplicationCmd
};
use BpmPlatform\Engine\Impl\Interceptor\{
    AbstractCommand,
    CommandContext
};

class DeleteDeploymentFailCmd extends AbstractCommand
{
    private $deploymentId;
    private $processApplicationReference;

    public function __construct(string $deploymentId, ProcessApplicationReferenceInterface $processApplicationReference)
    {
        $this->deploymentId = $deploymentId;
        $this->processApplicationReference = $processApplicationReference;
    }

    public function execute(CommandContext $commandContext)
    {
        $scope = $this;
        $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new RegisterDeploymentCmd($scope->deploymentId);
            $cmd->execute($commandContext);
        });
        if ($this->processApplicationReference != null) {
            $commandContext->runWithoutAuthorization(function () use ($scope, $commandContex) {
                $cmd = new RegisterProcessApplicationCmd($scope->deploymentId, $scope->processApplicationReference);
                $cmd->execute($commandContex);
            });
        }
        return null;
    }
}
