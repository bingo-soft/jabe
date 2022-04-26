<?php

namespace Jabe\Engine\Impl\Interceptor;

interface CommandExecutorInterface
{
    public function execute(CommandInterface $command);
}
