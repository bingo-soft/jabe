<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Interceptor\CommandInterface;

interface AcquireJobsCommandFactoryInterface
{
    public function getCommand(int $numJobsToAcquire): CommandInterface;
}
