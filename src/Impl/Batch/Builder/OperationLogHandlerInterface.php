<?php

namespace Jabe\Impl\Batch\Builder;

use Jabe\Impl\Interceptor\CommandContext;

interface OperationLogHandlerInterface
{
    /**
     * Callback to write the Operation Log.
     * @param commandContext can be used when writing the Operation Log
     */
    public function write(CommandContext $commandContext): void;
}
