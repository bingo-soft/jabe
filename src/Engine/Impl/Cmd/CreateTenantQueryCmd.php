<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Identity\TenantQueryInterface;
use Jabe\Engine\Impl\Interceptor\{
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
