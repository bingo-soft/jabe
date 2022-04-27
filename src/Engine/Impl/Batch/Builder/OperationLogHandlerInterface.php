<?php

namespace Jabe\Engine\Impl\Batch\Builder;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface OperationLogHandlerInterface
{
    /**
     * Callback to write the Operation Log.
     * @param commandContext can be used when writing the Operation Log
     */
    public function write(CommandContext $commandContext): void;
}
