<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Identity\WritableIdentityProviderInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

abstract class AbstractWritableIdentityServiceCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        // check identity service implementation
        if (!array_key_exists(WritableIdentityProviderInterface::class, $commandContext->getSessionFactories())) {
            throw new \Exception("This identity service implementation is read-only.");
        }
        $result = $this->executeCmd($commandContext);
        return $result;
    }

    abstract protected function executeCmd(CommandContext $commandContext);

    public function isRetryable(): bool
    {
        return false;
    }
}
