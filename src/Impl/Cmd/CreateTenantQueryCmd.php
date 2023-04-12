<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateTenantQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getReadOnlyIdentityProvider()
            ->createTenantQuery();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
