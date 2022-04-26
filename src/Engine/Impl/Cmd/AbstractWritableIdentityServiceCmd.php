<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Identity\WritableIdentityProviderInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

abstract class AbstractWritableIdentityServiceCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        // check identity service implementation
        if (!array_key_exists(WritableIdentityProviderInterface::class, $commandContext->getSessionFactories())) {
            throw new UnsupportedOperationException("This identity service implementation is read-only.");
        }

        $result = $this->executeCmd($commandContext);
        return $result;
    }

    abstract protected function executeCmd(CommandContext $commandContext);
}
