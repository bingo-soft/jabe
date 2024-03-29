<?php

namespace Jabe\Impl\Interceptor;

interface CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args);

    public function isRetryable(): bool;
}
