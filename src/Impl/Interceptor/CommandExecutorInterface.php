<?php

namespace Jabe\Impl\Interceptor;

interface CommandExecutorInterface
{
    public function execute(CommandInterface $command);
}
