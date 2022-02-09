<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\Impl\Cfg\TransactionListenerInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use BpmPlatform\Engine\Impl\Persistence\Entity\TimerEntity;

class RepeatingFailedJobListener implements TransactionListenerInterface
{
    protected $commandExecutor;
    protected $jobId;

    public function __construct(CommandExecutorInterface $commandExecutor, string $jobId)
    {
        $this->commandExecutor = $commandExecutor;
        $this->jobId = $jobId;
    }

    public function execute(CommandContext $commandContext): void
    {
        $cmd = new CreateNewTimerJobCommand($this->jobId);
        $this->commandExecutor->execute($cmd);
    }
}
