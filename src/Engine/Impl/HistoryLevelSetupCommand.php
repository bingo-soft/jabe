<?php

namespace BpmPlatform\Engine\Impl;

use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Interceptor\CommandInterface;

class HistoryLevelSetupCommand implements CommandInterface
{
    public function execute(CommandContext $commandContext): void
    {
    }
}
