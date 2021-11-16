<?php

namespace BpmPlatform\Engine\Impl\Cfg;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;

interface TransactionListenerInterface
{
    public function execute(CommandContext $commandContext): void;
}
