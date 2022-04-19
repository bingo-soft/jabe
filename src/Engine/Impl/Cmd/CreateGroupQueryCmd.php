<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Identity\GroupQueryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
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
}
