<?php

namespace Jabe\Impl\Interceptor;

abstract class AbstractCommand implements CommandInterface
{
    abstract public function execute(CommandContext $commandContext);

    /**
     * @return bool - true if the CrdbTransactionRetryInterceptor
     *   can make a transparent retry of this command upon failure
     *   with a CrdbTransactionRetryException (only used when running on CockroachDB).
     */
    public function isRetryable(): bool
    {
        return false;
    }
}
