<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Impl\Util\{
    ClockUtil,
    EnsureUtil
};

class ExtendLockOnExternalTaskCmd extends HandleExternalTaskCmd
{
    private $newLockTime;

    public function __construct(?string $externalTaskId, ?string $workerId, int $newLockTime)
    {
        parent::__construct($externalTaskId, $workerId);
        EnsureUtil::ensurePositive(BadUserRequestException::class, "lockTime", $newLockTime);
        $this->newLockTime = $newLockTime;
    }

    public function getErrorMessageOnWrongWorkerAccess(): ?string
    {
        return "The lock of the External Task " . $this->externalTaskId . " cannot be extended by worker '" . $this->workerId . "'";
    }

    protected function executeTask(ExternalTaskEntity $externalTask)
    {
        EnsureUtil::ensureGreaterThanOrEqual(
            "Cannot extend a lock that expired",
            "lockExpirationTime",
            $externalTask->getLockExpirationTime(),
            ClockUtil::getCurrentTime()->format('c')
        );
        $externalTask->extendLock($this->newLockTime);
    }
}
