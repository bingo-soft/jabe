<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Cfg\TransactionListenerInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Persistence\Entity\TimerEntity;

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
