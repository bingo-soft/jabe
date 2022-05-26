<?php

namespace Jabe\Engine\Impl\Persistence\Deploy;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Impl\Cfg\TransactionListenerInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class DeleteDeploymentFailListener implements TransactionListenerInterface
{
    protected $deploymentId;
    protected $processApplicationReference;
    protected $commandExecutor;

    public function __construct(string $deploymentId, ProcessApplicationReferenceInterface $processApplicationReference, CommandExecutorInterface $commandExecutor)
    {
        $this->deploymentId = $deploymentId;
        $this->processApplicationReference = $processApplicationReference;
        $this->commandExecutor = $commandExecutor;
    }

    public function execute(CommandContext $commandContext)
    {
        //we can not use commandContext parameter here, as it can be in inconsistent state
        $this->commandExecutor->execute(new DeleteDeploymentFailCmd($this->deploymentId, $this->processApplicationReference));
    }
}
