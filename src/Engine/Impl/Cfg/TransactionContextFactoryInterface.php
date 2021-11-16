<?php

namespace BpmPlatform\Engine\Impl\Cfg;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;

interface TransactionContextFactoryInterface
{
    public function openTransactionContext(CommandContext $commandContext): TransactionContextInterface;
}
