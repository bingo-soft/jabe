<?php

namespace Jabe\Impl;

use Jabe\Impl\Interceptor\CommandExecutorInterface;

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
