<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Identity\WritableIdentityProviderInterface;
use Jabe\Engine\Impl\Interceptor\{
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
