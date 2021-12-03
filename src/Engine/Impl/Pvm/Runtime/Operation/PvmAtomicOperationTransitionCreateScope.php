<?php

namespace BpmPlatform\Engine\Impl\Pvm\Runtime\Operation;

use BpmPlatform\Engine\Impl\Pvm\PvmActivityInterface;
use BpmPlatform\Engine\Impl\Pvm\Runtime\PvmExecutionImpl;

class PvmAtomicOperationTransitionCreateScope extends PvmAtomicOperationCreateScope
{
    public function isAsync(PvmExecutionImpl $execution): bool
    {
        $activity = $execution->getActivity();
        return $activity->isAsyncBefore();
    }

    public function getCanonicalName(): string
    {
        return "transition-create-scope";
    }

    protected function scopeCreated(PvmExecutionImpl $execution): void
    {
        $execution->performOperation(self::transitionNotifyListenerStart());
    }

    public function isAsyncCapable(): bool
    {
        return true;
    }
}
