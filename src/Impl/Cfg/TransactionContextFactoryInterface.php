<?php

namespace Jabe\Impl\Cfg;

use Jabe\Impl\Interceptor\CommandContext;

interface TransactionContextFactoryInterface
{
    public function openTransactionContext(CommandContext $commandContext): TransactionContextInterface;
}
