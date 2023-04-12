<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\CommandContext;

interface ProcessInstanceModificationInstructionInterface
{
    public function execute(CommandContext $commandContext, ...$args);
}
