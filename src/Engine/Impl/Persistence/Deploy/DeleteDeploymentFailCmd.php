<?php

namespace Jabe\Engine\Impl\Persistence\Deploy;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Impl\Cmd\{
    RegisterDeploymentCmd,
    RegisterProcessApplicationCmd
};
use Jabe\Engine\Impl\Interceptor\{
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
        if ($this->processApplicationReference !== null) {
            $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
                $cmd = new RegisterProcessApplicationCmd($scope->deploymentId, $scope->processApplicationReference);
                $cmd->execute($commandContext);
            });
        }
        return null;
    }
}
