<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateNativeUserQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getReadOnlyIdentityProvider()->createNativeUserQuery();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
