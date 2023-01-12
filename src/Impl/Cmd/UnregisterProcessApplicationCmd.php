<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
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
            ->unregisterProcessApplicationForDeployments($this->deploymentIds, $this->removeProcessesFromCache);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
