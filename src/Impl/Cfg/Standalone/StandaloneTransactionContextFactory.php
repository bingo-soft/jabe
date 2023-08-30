<?php

namespace Jabe\Impl\Cfg\Standalone;

use Jabe\Impl\Cfg\{
    TransactionContextInterface,
    TransactionContextFactoryInterface
};
use Jabe\Impl\Interceptor\CommandContext;

class StandaloneTransactionContextFactory implements TransactionContextFactoryInterface
{
    public function openTransactionContext(CommandContext $commandContext): TransactionContextInterface
    {
        return new StandaloneTransactionContext($commandContext);
    }
}
