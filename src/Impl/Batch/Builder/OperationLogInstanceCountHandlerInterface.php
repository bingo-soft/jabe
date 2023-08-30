<?php

namespace Jabe\Impl\Batch\Builder;

use Jabe\Impl\Interceptor\CommandContext;

interface OperationLogInstanceCountHandlerInterface
{
    /**
     * Callback to write the Operation Log.
     * @param commandContext can be used when writing the Operation Log
     * @param instanceCount that can be logged
     */
    public function write(CommandContext $commandContext, int $instanceCount): void;
}
