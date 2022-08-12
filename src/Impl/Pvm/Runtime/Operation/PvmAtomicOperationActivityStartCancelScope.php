<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationActivityStartCancelScope extends PvmAtomicOperationCancelActivity
{

    public function getCanonicalName(): string
    {
        return "activity-start-cancel-scope";
    }

    protected function activityCancelled(PvmExecutionImpl $execution): void
    {
        $execution->setActivityInstanceId(null);
        $execution->performOperation(self::activityStartCreateScope());
    }

    protected function getCancellingActivity(PvmExecutionImpl $execution): PvmActivityInterface
    {
        return $execution->getNextActivity();
    }

    public function isAsyncCapable(): bool
    {
        return false;
    }
}
