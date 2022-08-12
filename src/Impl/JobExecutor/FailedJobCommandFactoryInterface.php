<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Interceptor\CommandInterface;

interface FailedJobCommandFactoryInterface
{
    public function getCommand(string $jobId, \Throwable $exception): CommandInterface;
}
