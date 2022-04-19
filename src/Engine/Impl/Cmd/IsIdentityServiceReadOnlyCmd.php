<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Identity\WritableIdentityProviderInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class IsIdentityServiceReadOnlyCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return !array_key_exists(WritableIdentityProviderInterface::class, $commandContext->getSessionFactories());
    }
}
