<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Interceptor\CommandInterface;

interface FailedJobCommandFactoryInterface
{
    public function getCommand(string $jobId, \Throwable $exception): CommandInterface;
}
