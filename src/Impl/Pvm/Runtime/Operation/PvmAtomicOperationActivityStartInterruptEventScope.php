<?php

namespace Jabe\Impl\Pvm\Runtime\Operation;

use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationActivityStartInterruptEventScope extends PvmAtomicOperationInterruptScope
{
    public function getCanonicalName(): string
    {
        return "activity-start-interrupt-scope";
    }

    protected function scopeInterrupted(PvmExecutionImpl $execution): void
    {
        $execution->setActivityInstanceId(null);
        $execution->performOperation(self::activityStartCreateScope());
    }

    protected function getInterruptingActivity(PvmExecutionImpl $execution): PvmActivityInterface
    {
        $nextActivity = $execution->getNextActivity();
        $execution->setNextActivity(null);
        return $nextActivity;
    }
}
