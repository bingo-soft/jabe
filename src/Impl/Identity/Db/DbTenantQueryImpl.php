<?php

namespace Jabe\Impl\Identity\Db;

use Jabe\Impl\{
    Page,
    TenantQueryImpl
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class DbTenantQueryImpl extends TenantQueryImpl
{
    public function __construct(CommandExecutorInterface $commandExecutor = null)
    {
        parent::__construct($commandExecutor);
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findTenantCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, ?Page $page): array
    {
        $this->checkQueryOk();
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findTenantByQueryCriteria($this);
    }

    private function getIdentityProvider(CommandContext $commandContext): DbReadOnlyIdentityServiceProvider
    {
        return $commandContext->getReadOnlyIdentityProvider();
    }
}
