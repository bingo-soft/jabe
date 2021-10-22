<?php

namespace BpmPlatform\Engine\Impl\Interceptor;

abstract class AbstractCommand implements CommandInterface
{
    abstract public function execute(CommandContext $commandContext);

    /**
     * @return true if the {@link CrdbTransactionRetryInterceptor}
     *   can make a transparent retry of this command upon failure
     *   with a {@link CrdbTransactionRetryException} (only used when running on CockroachDB).
     */
    public function isRetryable(): bool
    {
        return false;
    }
}
