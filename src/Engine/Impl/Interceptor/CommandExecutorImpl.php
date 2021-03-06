<?php

namespace Jabe\Engine\Impl\Interceptor;

class CommandExecutorImpl extends CommandInterceptor
{
    public function execute(CommandInterface $command)
    {
        return $command->execute(Context::getCommandContext());
    }
}
