<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cmd\CommandLogger;

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
