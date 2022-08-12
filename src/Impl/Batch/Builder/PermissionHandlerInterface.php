<?php

namespace Jabe\Impl\Batch\Builder;

use Jabe\Impl\Interceptor\CommandContext;

interface PermissionHandlerInterface
{
    /**
     * Callback that performs the permission check.
     * @param commandContext can be used within the permission check
     */
    public function check(CommandContext $commandContext): void;
}
