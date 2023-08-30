<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Cmd\DefaultJobRetryCmd;
use Jabe\Impl\Interceptor\CommandInterface;

class DefaultFailedJobCommandFactory implements FailedJobCommandFactoryInterface
{
    public function getCommand(?string $jobId, \Throwable $exception): CommandInterface
    {
        return new DefaultJobRetryCmd($jobId, $exception);
    }
}
