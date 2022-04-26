<?php

namespace Jabe\Engine\Impl\JobExecutor;

interface ExceptionLoggingHandlerInterface
{
    public function exceptionWhileExecutingJob(string $jobId, \Throwable $exception): void;
}
