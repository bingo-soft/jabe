<?php

namespace Jabe\Engine\Impl\Cfg;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface TransactionContextFactoryInterface
{
    public function openTransactionContext(CommandContext $commandContext): TransactionContextInterface;
}
