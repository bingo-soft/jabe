<?php

namespace Jabe\Engine\Impl\Identity\Db;

use Jabe\Engine\Impl\{
    GroupQueryImpl,
    Page
};
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class DbGroupQueryImpl extends GroupQueryImpl
{
    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findGroupCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findGroupByQueryCriteria($this);
    }

    protected function getIdentityProvider(CommandContext $commandContext): DbReadOnlyIdentityServiceProvider
    {
        return $commandContext->getReadOnlyIdentityProvider();
    }
}
