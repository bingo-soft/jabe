<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Identity\WritableIdentityProviderInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class IsIdentityServiceReadOnlyCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return !array_key_exists(WritableIdentityProviderInterface::class, $commandContext->getSessionFactories());
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
