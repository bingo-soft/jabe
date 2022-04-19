<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateNativeUserQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext->getReadOnlyIdentityProvider()->createNativeUserQuery();
    }
}
