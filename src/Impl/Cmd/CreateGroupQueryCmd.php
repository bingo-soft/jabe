<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateGroupQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getReadOnlyIdentityProvider()
            ->createGroupQuery();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
