<?php

namespace Jabe\Impl\Cfg;

use Jabe\Impl\Interceptor\CommandContext;

interface TransactionListenerInterface
{
    public function execute(CommandContext $commandContext, ...$args);
}
