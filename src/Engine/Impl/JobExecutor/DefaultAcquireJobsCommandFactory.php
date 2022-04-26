<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cmd\AcquireJobsCmd;
use Jabe\Engine\Impl\Interceptor\CommandInterface;

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
