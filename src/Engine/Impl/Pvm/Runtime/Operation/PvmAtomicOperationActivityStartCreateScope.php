<?php

namespace Jabe\Engine\Impl\Pvm\Runtime\Operation;

use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationActivityStartCreateScope extends PvmAtomicOperationCreateScope
{
    public function isAsync(PvmExecutionImpl $execution): bool
    {
        $activity = $execution->getActivity();
        return $activity->isAsyncBefore();
    }

    public function isAsyncCapable(): bool
    {
        return true;
    }

    public function getCanonicalName(): string
    {
        return "activity-start-create-scope";
    }

    protected function scopeCreated(PvmExecutionImpl $execution): void
    {
        $execution->setIgnoreAsync(false);
        $execution->performOperation(self::activityStart());
    }
}
