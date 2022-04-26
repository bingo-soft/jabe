<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\Persistence\Entity\ExternalTaskEntity;
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil
};

class ExtendLockOnExternalTaskCmd extends HandleExternalTaskCmd
{
    private $newLockTime;

    public function __construct(string $externalTaskId, string $workerId, int $newLockTime)
    {
        parent::__construct($externalTaskId, $workerId);
        EnsureUtil::ensurePositive(BadUserRequestException::class, "lockTime", $newLockTime);
        $this->newLockTime = $newLockTime;
    }

    public function getErrorMessageOnWrongWorkerAccess(): string
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
