<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\CommandInterface;

interface AcquireJobsCommandFactoryInterface
{
    public function getCommand(int $numJobsToAcquire): CommandInterface;
}
