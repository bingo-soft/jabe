<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\Context\Context;

class CommandExecutorImpl extends CommandInterceptor
{
    public function execute(CommandInterface $command, ...$args)
    {
        if (empty($args) && !empty($this->getState())) {
            $args = $this->getState();
        }
        return $command->execute(Context::getCommandContext(), ...$args);
    }
}
