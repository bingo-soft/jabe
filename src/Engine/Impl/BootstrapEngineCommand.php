<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineBootstrapCommandInterface;
use Jabe\Engine\Impl\Interceptor\CommandContext;

class BootstrapEngineCommand implements ProcessEngineBootstrapCommandInterface
{
    public function execute(CommandContext $commandContext): void
    {
    }
}
