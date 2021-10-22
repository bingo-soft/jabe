<?php

namespace BpmPlatform\Engine\Impl;

use BpmPlatform\Engine\ProcessEngineBootstrapCommandInterface;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;

class BootstrapEngineCommand implements ProcessEngineBootstrapCommandInterface
{
    public function execute(CommandContext $commandContext): void
    {
    }
}
