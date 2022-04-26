<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Identity\GroupQueryInterface;
use Jabe\Engine\Impl\Interceptor\{
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
