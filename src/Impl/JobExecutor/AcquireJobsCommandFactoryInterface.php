<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Interceptor\CommandInterface;

interface AcquireJobsCommandFactoryInterface
{
    public function getCommand(int $numJobsToAcquire): CommandInterface;
}
