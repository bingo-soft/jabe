<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Identity\TenantQueryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateTenantQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getReadOnlyIdentityProvider()
            ->createTenantQuery();
    }
}
