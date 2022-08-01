<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil
};

class LockExternalTaskCmd extends HandleExternalTaskCmd
{
    protected $lockDuration;

    public function __construct(string $externalTaskId, string $workerId, int $lockDuration)
    {
        parent::__construct($externalTaskId, $workerId);
        $this->lockDuration = $lockDuration;
    }

    protected function executeTask(ExternalTaskEntity $externalTask)
    {
        $externalTask->lock($this->workerId, $this->lockDuration);
    }

    public function getErrorMessageOnWrongWorkerAccess(): string
    {
        return "External Task " . $this->externalTaskId . " cannot be locked by worker '" . $this->workerId;
    }

    /*
      Report a worker violation only if another worker has locked the task,
      and the lock expiration time is still not expired.
     */
    protected function validateWorkerViolation(ExternalTaskEntity $externalTask): bool
    {
        $existingWorkerId = $externalTask->getWorkerId();
        $existingLockExpirationTime = $externalTask->getLockExpirationTime();

        // check if another worker is attempting to lock the same task
        $workerValidation = $existingWorkerId !== null && $this->workerId != $existingWorkerId;
        // and check if an existing lock is already expired
        $lockValidation = $existingLockExpirationTime !== null
            && ClockUtil::getCurrentTime() <= new \DateTime($existingLockExpirationTime);

        return $workerValidation && $lockValidation;
    }

    protected function validateInput(): void
    {
        parent::validateInput();
        EnsureUtil::ensurePositive("lockDuration", "lockDuration", $this->lockDuration);
    }
}
