<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Cmd\AcquireJobsCmd;
use BpmPlatform\Engine\Impl\Interceptor\CommandInterface;

class DefaultAcquireJobsCommandFactory implements AcquireJobsCommandFactoryInterface
{
    protected $jobExecutor;

    public function __construct(JobExecutor $jobExecutor)
    {
        $this->jobExecutor = $jobExecutor;
    }

    public function getCommand(int $numJobsToAcquire): CommandInterface
    {
        return new AcquireJobsCmd($this->jobExecutor, $numJobsToAcquire);
    }
}
