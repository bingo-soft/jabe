<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class UnregisterProcessApplicationCmd implements CommandInterface
{
    protected $removeProcessesFromCache;
    protected $deploymentIds = [];

    public function __construct($deploymentIds, bool $removeProcessesFromCache)
    {
        $this->deploymentIds = is_array($deploymentIds) ? $deploymentIds : [$deploymentIds];
        $this->removeProcessesFromCache = $removeProcessesFromCache;
    }

    public function execute(CommandContext $commandContext)
    {
        if (empty($this->deploymentIds)) {
            throw new ProcessEngineException("Deployment Ids cannot be null.");
        }
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkUnregisterProcessApplication");
        Context::getProcessEngineConfiguration()
            ->getProcessApplicationManager()
            ->unregisterProcessApplicationForDeployments($this->deploymentIds, $removeProcessesFromCache);
        return null;
    }
}
