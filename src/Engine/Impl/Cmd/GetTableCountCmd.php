<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetTableCountCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableCount");

        return $commandContext
            ->getTableDataManager()
            ->getTableCount();
    }
}
