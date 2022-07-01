<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface ProcessInstanceModificationInstructionInterface
{
    public function execute(CommandContext $commandContext);
}
