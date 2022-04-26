<?php

namespace Jabe\Engine\Impl\Cfg;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface TransactionListenerInterface
{
    public function execute(CommandContext $commandContext): void;
}
