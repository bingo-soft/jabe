<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

interface CommandExecutorInterface
{
    public function execute(CommandInterface $command);
}
