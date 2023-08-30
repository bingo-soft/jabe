<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTableCountCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableCount");

        return $commandContext
            ->getTableDataManager()
            ->getTableCount();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
