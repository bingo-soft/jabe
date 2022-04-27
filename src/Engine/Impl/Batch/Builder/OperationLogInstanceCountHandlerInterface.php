<?php

namespace Jabe\Engine\Impl\Batch\Builder;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface OperationLogInstanceCountHandlerInterface
{
    /**
     * Callback to write the Operation Log.
     * @param commandContext can be used when writing the Operation Log
     * @param instanceCount that can be logged
     */
    public function write(CommandContext $commandContext, int $instanceCount): void;
}
