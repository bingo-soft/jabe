<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cmd\DefaultJobRetryCmd;
use Jabe\Engine\Impl\Interceptor\CommandInterface;

class DefaultFailedJobCommandFactory implements FailedJobCommandFactoryInterface
{
    public function getCommand(string $jobId, \Throwable $exception): CommandInterface
    {
        return new DefaultJobRetryCmd($jobId, $exception);
    }
}
