<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cmd\CommandLogger;

class LogInterceptor extends CommandInterceptor
{
    //private static final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function execute(CommandInterface $command)
    {
        //LOG.debugStartingCommand(command);
        try {
            return $this->next->execute($command);
        } finally {
            //LOG.debugFinishingCommand(command);
        }
    }
}
