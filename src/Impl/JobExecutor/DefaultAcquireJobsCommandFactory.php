<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Cmd\AcquireJobsCmd;
use Jabe\Impl\Interceptor\CommandInterface;

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
