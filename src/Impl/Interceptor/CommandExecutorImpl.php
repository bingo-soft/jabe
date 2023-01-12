<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Context\Context;

class CommandExecutorImpl extends CommandInterceptor
{
    public function execute(CommandInterface $command)
    {
        return $command->execute(Context::getCommandContext());
    }
}
