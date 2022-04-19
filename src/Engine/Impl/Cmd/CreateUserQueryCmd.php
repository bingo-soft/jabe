<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateUserQueryCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getReadOnlyIdentityProvider()
            ->createUserQuery();
    }
}
