<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Interceptor\CommandExecutorInterface;

class ServiceImpl
{
    protected $commandExecutor;

    public function getCommandExecutor(): CommandExecutorInterface
    {
        return $this->commandExecutor;
    }

    public function setCommandExecutor(CommandExecutorInterface $commandExecutor): void
    {
        $this->commandExecutor = $commandExecutor;
    }
}
