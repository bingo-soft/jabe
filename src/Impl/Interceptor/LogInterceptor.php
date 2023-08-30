<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cmd\CommandLogger;

class LogInterceptor extends CommandInterceptor
{
    //private static final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function execute(CommandInterface $command, ...$args)
    {
        //LOG.debugStartingCommand(command);
        try {
            if (empty($args) && !empty($this->getState())) {
                $args = $this->getState();
            }
            return $this->next->execute($command, ...$args);
        } finally {
            //LOG.debugFinishingCommand(command);
        }
    }
}
