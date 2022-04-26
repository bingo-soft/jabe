<?php

namespace Jabe\Engine\Impl\Interceptor;

interface CommandInterface
{
    public function execute(CommandContext $commandContext);

    public function isRetryable(): bool;
}
