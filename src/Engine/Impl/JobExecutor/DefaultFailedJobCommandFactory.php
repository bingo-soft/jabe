<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Cmd\DefaultJobRetryCmd;
use BpmPlatform\Engine\Impl\Interceptor\CommandInterface;

class DefaultFailedJobCommandFactory implements FailedJobCommandFactoryInterface
{
    public function getCommand(string $jobId, \Throwable $exception): CommandInterface
    {
        return new DefaultJobRetryCmd($jobId, $exception);
    }
}
