<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};

class FailedJobListenerCmd implements CommandInterface
{
    protected $jobId;
    protected $cmd;
    protected $listener;

    public function __construct(?string $jobId, CommandInterface $cmd, FailedJobListener $listener)
    {
        $this->jobId = $jobId;
        $this->cmd = $cmd;
        $this->listener = $listener;
    }

    public function execute(CommandContext $commandContext, ...$args)
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

    public function isRetryable(): bool
    {
        return false;
    }
}
