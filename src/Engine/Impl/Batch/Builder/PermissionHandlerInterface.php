<?php

namespace Jabe\Engine\Impl\Batch\Builder;

use Jabe\Engine\Impl\Interceptor\CommandContext;

interface PermissionHandlerInterface
{
    /**
     * Callback that performs the permission check.
     * @param commandContext can be used within the permission check
     */
    public function check(CommandContext $commandContext): void;
}
