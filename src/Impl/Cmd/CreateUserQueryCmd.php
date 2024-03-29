<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateUserQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getReadOnlyIdentityProvider()
            ->createUserQuery();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
