<?php

namespace Jabe\Impl\Cmd;

use Jabe\OptimisticLockingException;
use Jabe\Impl\Cfg\TransactionState;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\JobExecutor\MessageAddedNotification;
use Jabe\Impl\Persistence\Entity\JobEntity;

abstract class JobRetryCmd implements CommandInterface
{
    protected $jobId;
    protected $exception;

    public function __construct(?string $jobId, \Throwable $exception)
    {
        $this->jobId = $jobId;
        $this->exception = $exception;
    }

    protected function getJob(): ?JobEntity
    {
        return Context::getCommandContext()
            ->getJobManager()
            ->findJobById($this->jobId);
    }

    protected function logException(JobEntity $job): void
    {
        if ($this->exception !== null) {
            $job->setExceptionMessage($this->exception->getMessage());
            $job->setExceptionStacktrace($this->exception->getTraceAsString());
        }
    }

    protected function decrementRetries(JobEntity $job): void
    {
        if ($this->exception === null || $this->shouldDecrementRetriesFor($this->exception)) {
            $job->setRetries($job->getRetries() - 1);
        }
    }

    protected function getExceptionStacktrace(): ?string
    {
        return ExceptionUtil::getExceptionStacktrace($this->exception);
    }

    protected function shouldDecrementRetriesFor(\Throwable $t): bool
    {
        return !($t instanceof OptimisticLockingException);
    }

    protected function notifyAcquisition(CommandContext $commandContext): void
    {
        $jobExecutor = Context::getProcessEngineConfiguration()->getJobExecutor();
        $messageAddedNotification = new MessageAddedNotification($jobExecutor);
        $transactionContext = $commandContext->getTransactionContext();
        $transactionContext->addTransactionListener(TransactionState::COMMITTED, $messageAddedNotification);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
