<?php

namespace Jabe\Engine\Impl\Interceptor;

interface CommandContextListenerInterface
{
    public function onCommandContextClose(CommandContext $commandContext): void;

    public function onCommandFailed(CommandContext $commandContext, \Throwable $t): void;
}
