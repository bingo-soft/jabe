<?php

namespace Jabe\Impl;

use Jabe\ExternalTaskServiceInterface;
use Jabe\Batch\BatchInterface;
use Jabe\ExternalTask\{
    ExternalTaskQueryInterface,
    ExternalTaskQueryBuilderInterface,
    UpdateExternalTaskRetriesSelectBuilderInterface
};
use Jabe\Impl\Cmd\{
    LockExternalTaskCmd,
    CompleteExternalTaskCmd,
    HandleExternalTaskFailureCmd,
    HandleExternalTaskBpmnErrorCmd,
    UnlockExternalTaskCmd,
    SetExternalTaskRetriesCmd,
    SetExternalTaskPriorityCmd,
    GetTopicNamesCmd,
    GetExternalTaskErrorDetailsCmd,
    ExtendLockOnExternalTaskCmd
};
use Jabe\Impl\ExternalTask\ExternalTaskQueryTopicBuilderImpl;

class ExternalTaskServiceImpl extends ServiceImpl implements ExternalTaskServiceInterface
{
    public function fetchAndLock(int $maxTasks, string $workerId, bool $usePriority = false): ExternalTaskQueryBuilderInterface
    {
        return new ExternalTaskQueryTopicBuilderImpl($this->commandExecutor, $workerId, $maxTasks, $usePriority);
    }

    public function lock(string $externalTaskId, string $workerId, int $lockDuration): void
    {
        $this->commandExecutor->execute(new LockExternalTaskCmd($externalTaskId, $workerId, $lockDuration));
    }

    public function complete(string $externalTaskId, string $workerId, array $variables = [], array $localVariables = []): void
    {
        $this->commandExecutor->execute(new CompleteExternalTaskCmd($externalTaskId, $workerId, $variables, $localVariables));
    }

    public function handleFailure(string $externalTaskId, string $workerId, string $errorMessage, string $errorDetails, int $retries, int $retryDuration, array $variables = [], array $localVariables = []): void
    {
        $this->commandExecutor->execute(new HandleExternalTaskFailureCmd(externalTaskId, workerId, errorMessage, errorDetails, retries, retryDuration, variables, localVariables));
    }

    public function handleBpmnError(string $externalTaskId, string $workerId, string $errorCode, string $errorMessage = null, array $variables = []): void
    {
        $this->commandExecutor->execute(new HandleExternalTaskBpmnErrorCmd($externalTaskId, $workerId, $errorCode, $errorMessage, $variables));
    }

    public function unlock(string $externalTaskId): void
    {
        $this->commandExecutor->execute(new UnlockExternalTaskCmd($externalTaskId));
    }

    public function setPriority(string $externalTaskId, int $priority): void
    {
        $this->commandExecutor->execute(new SetExternalTaskPriorityCmd($externalTaskId, $priority));
    }

    public function createExternalTaskQuery(): ExternalTaskQueryInterface
    {
        return new ExternalTaskQueryImpl($this->commandExecutor);
    }

    public function getTopicNames(bool $withLockedTasks = false, bool $withUnlockedTasks = false, bool $withRetriesLeft = false): array
    {
        return $this->commandExecutor->execute(new GetTopicNamesCmd($withLockedTasks, $withUnlockedTasks, $withRetriesLeft));
    }

    public function getExternalTaskErrorDetails(string $externalTaskId): string
    {
        return $this->commandExecutor->execute(new GetExternalTaskErrorDetailsCmd($externalTaskId));
    }

    public function setRetries($externalTaskIdOrIds, int $retries, bool $writeUserOperationLog = true): void
    {
        if (is_array($externalTaskIdOrIds)) {
            $this->updateRetries()
                ->externalTaskIds($externalTaskIdOrIds)
                ->set($retries);
        } else {
            $this->commandExecutor->execute(new SetExternalTaskRetriesCmd($externalTaskIdOrIds, $retries, $writeUserOperationLog));
        }
    }

    public function setRetriesAsync(array $externalTaskIds, ExternalTaskQueryInterface $externalTaskQuery, int $retries): BatchInterface
    {
        return $this->updateRetries()
            ->externalTaskIds($externalTaskIds)
            ->externalTaskQuery($externalTaskQuery)
            ->setAsync($retries);
    }

    public function updateRetries(): UpdateExternalTaskRetriesSelectBuilderInterface
    {
        return new UpdateExternalTaskRetriesBuilderImpl($this->commandExecutor);
    }

    public function extendLock(string $externalTaskId, string $workerId, int $lockDuration): void
    {
        $this->commandExecutor->execute(new ExtendLockOnExternalTaskCmd($externalTaskId, $workerId, $lockDuration));
    }
}
