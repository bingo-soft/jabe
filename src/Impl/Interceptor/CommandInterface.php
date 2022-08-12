<?php

namespace Jabe\Impl\Interceptor;

interface CommandInterface
{
    public function execute(CommandContext $commandContext);

    public function isRetryable(): bool;
}
