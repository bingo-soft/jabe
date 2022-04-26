<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\CommandInterface;

interface FailedJobCommandFactoryInterface
{
    public function getCommand(string $jobId, \Throwable $exception): CommandInterface;
}
