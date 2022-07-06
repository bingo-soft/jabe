<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};

class FailedJobListenerCmd implements CommandInterface
{
    protected $jobId;
    protected $cmd;
    protected $listener;

    public function __construct(string $jobId, CommandInterface $cmd, FailedJobListener $listener)
    {
        $this->jobId = $jobId;
        $this->cmd = $cmd;
        $this->listener = $listener;
    }

    public function execute(CommandContext $commandContext)
    {
        $job = $commandContext
            ->getJobManager()
            ->findJobById($this->jobId);

        if ($job !== null) {
            $job->setFailedActivityId($this->listener->getJobFailureCollector()->getFailedActivityId());
            $this->listener->fireHistoricJobFailedEvt($job);
            $this->cmd->execute($commandContext);
        } else {
            //LOG.debugFailedJobNotFound(jobId);
        }
        return null;
    }
}
