<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\CommandLogger;

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
