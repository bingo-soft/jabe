<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CreateNewTimerJobCommand implements CommandInterface
{
    protected $jobId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function execute(CommandContext $commandContext)
    {
        $failedJob = $commandContext
            ->getJobManager()
            ->findJobById($this->jobId);

        $newDueDate = $failedJob->calculateRepeat();

        if ($newDueDate != null) {
            $failedJob->createNewTimerJob($newDueDate);

            // update configuration of failed job
            $config = $failedJob->getJobHandlerConfiguration();
            $config->setFollowUpJobCreated(true);
            $failedJob->setJobHandlerConfiguration($config);
        }

        return null;
    }
}
